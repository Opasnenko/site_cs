<?php
	/**
	 * @author Pavel Djundik
	 *
	 * @link https://xpaw.me
	 * @link https://github.com/xPaw/PHP-Source-Query
	 *
	 * @license GNU Lesser General Public License, version 2.1
	 *
	 * @internal
	 */

	/**
	 * Class GoldSourceRcon
	 *
	 * @package xPaw\SourceQuery
	 *
	 * @uses xPaw\SourceQuery\Exception\AuthenticationException
	 * @uses xPaw\SourceQuery\Exception\InvalidPacketException
	 */
	class GoldSourceRcon
	{
		/**
		 * Points to socket class
		 * 
		 * @var BaseSocket
		 */
		private $Socket;
		
		private string $RconPassword = '';
		private string $RconChallenge = '';
		
		public function __construct( BaseSocket $Socket )
		{
			$this->Socket = $Socket;
		}
		
		public function Close( ) : void
		{
			$this->RconChallenge = '';
			$this->RconPassword  = '';
		}
		
		public function Open( ) : void
		{
			//
		}
		
		public function Write( int $Header, string $String = '' ) : bool
		{
			$Command = Pack( 'cccca*', 0xFF, 0xFF, 0xFF, 0xFF, $String );
			$Length  = StrLen( $Command );
			
			return $Length === FWrite( $this->Socket->Socket, $Command, $Length );
		}
		
		/**
		 * @param int $Length
		 * @throws AuthenticationException
		 * @return Buffer
		 */
		public function Read( int $Length = 1400 ) : Buffer
		{
			// GoldSource RCON has same structure as Query
			$Buffer = $this->Socket->Read( );

			$StringBuffer = '';
			$ReadMore = false;

			// There is no indentifier of the end, so we just need to continue reading
			do
			{
				$ReadMore = $Buffer->Remaining( ) > 0;

				if( $ReadMore )
				{
					if( $Buffer->GetByte( ) !== SourceQuery::S2A_RCON )
					{
						throw new InvalidPacketException( 'Invalid rcon response.', InvalidPacketException::PACKET_HEADER_MISMATCH );
					}

					$Packet = $Buffer->Get( );
					$StringBuffer .= $Packet;
					//$StringBuffer .= SubStr( $Packet, 0, -2 );

					// Let's assume if this packet is not long enough, there are no more after this one
					$ReadMore = StrLen( $Packet ) > 1000; // use 1300?

					if( $ReadMore )
					{
						/*
						 * Догадка о продолжении может ошибаться на границе размера пакета.
						 * Если сервер больше ничего не пришлёт, следующее чтение уйдёт в
						 * таймаут — а команда при этом уже реально выполнена и ответ уже
						 * получен в $StringBuffer. Не считаем это фатальной ошибкой, просто
						 * останавливаем накопление и возвращаем то, что уже прочитано.
						 */
						try
						{
							$Buffer = $this->Socket->Read( );
						}
						catch( InvalidPacketException $e )
						{
							$ReadMore = false;
						}
					}
				}
			}
			while( $ReadMore );
			
			$Trimmed = trim( $StringBuffer );
			
			if( $Trimmed === 'Bad rcon_password.' )
			{
				throw new AuthenticationException( $Trimmed, AuthenticationException::BAD_PASSWORD );
			}
			else if( $Trimmed === 'You have been banned from this server.' )
			{
				throw new AuthenticationException( $Trimmed, AuthenticationException::BANNED );
			}
			
			$Buffer->Set( $Trimmed );
			
			return $Buffer;
		}
		
		public function Command( string $Command ) : string
		{
			if( !$this->RconChallenge )
			{
				throw new AuthenticationException( 'Tried to execute a RCON command before successful authorization.', AuthenticationException::BAD_PASSWORD );
			}

			$EscapedPassword = str_replace( '"', '\\"', $this->RconPassword );

			if( !$this->Write( 0, 'rcon ' . $this->RconChallenge . ' "' . $EscapedPassword . '" ' . $Command . "\0" ) )
			{
				throw new SocketException( 'Could not write rcon command to socket.', SocketException::CONNECTION_FAILED );
			}

			$Buffer = $this->Read( );

			return $Buffer->Get( );
		}

		public function Authorize( string $Password ) : void
		{
			$this->RconPassword = $Password;

			if( !$this->Write( 0, 'challenge rcon' ) )
			{
				throw new SocketException( 'Could not write challenge request to socket.', SocketException::CONNECTION_FAILED );
			}

			$Buffer = $this->Socket->Read( );
			$Response = $Buffer->Get( );

			if( StrPos( $Response, 'challenge rcon' ) !== 0 )
			{
				throw new AuthenticationException( 'Failed to get RCON challenge.', AuthenticationException::BAD_PASSWORD );
			}

			$this->RconChallenge = Trim( SubStr( $Response, 14 ) );
		}
	}
