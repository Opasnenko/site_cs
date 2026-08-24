<?php

class OurSourceQuery extends SourceQuery {

	const RCON_TIMEOUT = 3;

	private $server = null;

	public function setServer($server) {
		$this->server = $server;

		return $this;
	}

	public function isServerCanWorkWithRcon() {
		if(
			empty($this->server->id)
			|| $this->server->rcon == 2
			|| empty($this->server->rcon_password)
		) {
			return false;
		} else {
			return true;
		}
	}

	public function checkConnect() {
		$port = (int) $this->server->port;

		if($this->server->game == 'Counter-Strike: 1.6') {
			$this->Connect($this->server->ip, $port, self::RCON_TIMEOUT, SourceQuery::GOLDSOURCE);
		} else {
			$this->Connect($this->server->ip, $port, self::RCON_TIMEOUT, SourceQuery::SOURCE);
		}

		return $this;
	}

	public function auth() {
		$this->SetRconPassword($this->server->rcon_password);

		return $this;
	}

	public function send($command) {
		$answer = $this->Rcon($command);
		$this->log($command, $answer);

		return $answer;
	}

	/**
	 * Отправляет rcon-команду с одной автоматической повторной попыткой
	 * при сетевой ошибке (таймаут/потеря UDP-пакета) — переустанавливает
	 * соединение и заново авторизуется перед повтором.
	 *
	 * @throws Throwable Исключение последней неудачной попытки
	 */
	public function sendWithRetry($command) {
		$attempts = 2;
		$lastError = null;

		for($i = 0; $i < $attempts; $i++) {
			try {
				if($i > 0) {
					$this->Disconnect();
				}

				$this->checkConnect()->auth();
				return $this->send($command);
			} catch(AuthenticationException $e) {
				throw $e;
			} catch(Throwable $e) {
				$lastError = $e;
			}
		}

		throw $lastError;
	}

	/**
	 * Быстрая проверка доступности rcon (без записи в консоль сервера) —
	 * пытается получить challenge и авторизоваться, ничего не выполняя.
	 */
	public function testConnection() {
		$this->checkConnect()->auth();
		$this->Disconnect();
		return true;
	}

	public function reloadAdmins($server = null) {
		if(is_null($this->server)) {
			$this->setServer(
				(new ServersManager())->getServer($server)
			);
		}

		$this->checkConnect();
		$this->auth();

		$command = (new ServerCommands())
			->getCommandBySlug(
				ServerCommands::RELOAD_ADMINS_COMMAND_SLUG,
				$this->server->id
			);

		$command = empty($command->command) ? '' : $command->command;

		$answer = $this->send($command);
		$this->Disconnect();

		return $answer;
	}

	public function log($command, $answer = null)
	{
		$file = get_log_file_name("rcon_log_" . $this->server->id);

		if(isset($_SESSION['id']) and isset($_SESSION['login'])) {
			$user = $_SESSION['login'] . ' - ' . $_SESSION['id'];
		} else {
			$user = 'Админ Центр';
		}

		$line = "[" . date("Y-m-d H:i:s") . " | Пользователь: " . $user . "] : [Команда: " . clean($command, null) . "]";
		if($answer !== null) {
			$answerClean = trim(preg_replace('/[\r\n]+/', ' ', (string) $answer));
			$line .= " : [Ответ: " . clean($answerClean, null) . "]";
		}
		$line .= " \r\n";

		if(function_exists('pb_append_log_line')) {
			pb_append_log_line($file, $line);
		} else {
			$dir = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . "/logs";
			if(!is_dir($dir)) { @mkdir($dir, 0755, true); }
			@file_put_contents($dir . "/" . basename($file), $line, FILE_APPEND | LOCK_EX);
		}
	}
}