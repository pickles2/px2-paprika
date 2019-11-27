<?php
/**
 * class log
 *
 * Paprika Framework のコアオブジェクトの1つ `$log` のオブジェクトクラスを定義します。
 *
 * @author Tomoya Koyanagi <tomk79@gmail.com>
 */
namespace picklesFramework2\paprikaFramework\fw;

/**
 * Log
 *
 * Paprika Framework のコアオブジェクトの1つ `$log` のオブジェクトクラスです。
 * このオブジェクトは、Paprika Framework の初期化処理の中で自動的に生成され、`$paprika` の内部に格納されます。
 *
 * メソッド `$paprika->bowl()` を通じてアクセスします。
 *
 * @author Tomoya Koyanagi <tomk79@gmail.com>
 */
class log{

	/**
	 * Paprikaオブジェクト
	 */
	private $paprika;

	/**
	 * ログイベントハンドラ
	 */
	private $log_event_handler;


	/**
	 * Constructor
	 */
	public function __construct( $paprika ){
		$this->paprika = $paprika;
	}

	/**
	 * エラー出力先ディレクトリのパスを取得する
	 */
	private function get_realpath_logdir(){
		$realpath_logs = $this->paprika->conf('realpath_log_dir');
		if( !is_dir($realpath_logs) || !is_writable($realpath_logs) ){
			$realpath_logs = $this->paprika->env()->realpath_homedir.'logs/';
			if( !is_dir($realpath_logs) ){
				mkdir($realpath_logs);
			}
		}

		$realpath_logs = $this->paprika->fs()->get_realpath( $realpath_logs.'/' );

		if( !is_writable($realpath_logs) ){
			// ログディレクトリに書き込みができない場合の臨時処置
			trigger_error('Log directory is NOT exists, or NOT writable.', E_USER_ERROR );
			$realpath_logs = __DIR__.'/__logs/';
			if( !is_dir($realpath_logs) ){
				mkdir($realpath_logs);
			}
			if( !is_writable($realpath_logs) ){
				trigger_error('[ERROR] Log was NOT writable!', E_USER_ERROR );
				exit;
			}
			trigger_error('see more: '.$realpath_logs, E_USER_ERROR );
		}
		return $realpath_logs;
	}

	/**
	 * Fatal Errorレベルのログを保存する
	 */
	public function fatal( $message = null ){
		$backtrace = debug_backtrace();
		$file = $backtrace[0]['file'];
		$line = $backtrace[0]['line'];
		$this->save_log($message, $file, $line, 'fatal');
	}

	/**
	 * Errorレベルのログを保存する
	 */
	public function error( $message = null ){
		$backtrace = debug_backtrace();
		$file = $backtrace[0]['file'];
		$line = $backtrace[0]['line'];
		$this->save_log($message, $file, $line, 'error');
	}

	/**
	 * Warningレベルのログを保存する
	 */
	public function warn( $message = null ){
		$backtrace = debug_backtrace();
		$file = $backtrace[0]['file'];
		$line = $backtrace[0]['line'];
		$this->save_log($message, $file, $line, 'warn');
	}

	/**
	 * Infoレベルのログを保存する
	 */
	public function info( $message = null ){
		$backtrace = debug_backtrace();
		$file = $backtrace[0]['file'];
		$line = $backtrace[0]['line'];
		$this->save_log($message, $file, $line, 'info');
	}

	/**
	 * Debugレベルのログを保存する
	 */
	public function debug( $message = null ){
		$backtrace = debug_backtrace();
		$file = $backtrace[0]['file'];
		$line = $backtrace[0]['line'];
		$this->save_log($message, $file, $line, 'debug');
	}

	/**
	 * Traceレベルのログを保存する
	 */
	public function trace( $message = null ){
		$backtrace = debug_backtrace();
		$file = $backtrace[0]['file'];
		$line = $backtrace[0]['line'];
		$this->save_log($message, $file, $line, 'trace');
	}

	/**
	 * ログ書き込みイベントハンドラをセットする
	 */
	public function set_log_handler( $func ){
		if( !is_callable($func) ){
			return false;
		}
		$this->log_event_handler = $func;
		return true;
	}

	/**
	 * ログを保存する
	 */
	private function save_log( $message, $file, $line, $level ){
		$realpath_logs = $this->get_realpath_logdir();
		$level = strtolower($level);
		$log = '';
		$log .= date('c');
		$log .= '	'.getmypid();
		$log .= '	'.ucfirst($level);
		$log .= '	'.$message;
		$log .= '	'.$file.' on line '.$line;
		error_log( $log."\n", 3, $realpath_logs.date('Y-m-d').'-all.log' );

		switch( $level ){
			case 'fatal':
				error_log( $log."\n", 3, $realpath_logs.'FATAL-'.date('Y-m-d').'.log' );
				break;
			case 'error':
				error_log( $log."\n", 3, $realpath_logs.'Error-'.date('Y-m-d').'.log' );
				break;
			case 'warn':
				error_log( $log."\n", 3, $realpath_logs.'warn-'.date('Y-m-d').'.log' );
				break;
		}

		if( is_callable($this->log_event_handler) ){
			call_user_func_array( $this->log_event_handler, func_get_args());
		}
	}

}
