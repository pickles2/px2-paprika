<?php
/**
 * class bowl
 *
 * Paprika Framework のコアオブジェクトの1つ `$bowl` のオブジェクトクラスを定義します。
 *
 * @author Tomoya Koyanagi <tomk79@gmail.com>
 */
namespace picklesFramework2\paprikaFramework\fw;

/**
 * Bowl
 *
 * Paprika Framework のコアオブジェクトの1つ `$bowl` のオブジェクトクラスです。
 * このオブジェクトは、Paprika Framework の初期化処理の中で自動的に生成され、`$paprika` の内部に格納されます。
 *
 * メソッド `$paprika->bowl()` を通じてアクセスします。
 *
 * @author Tomoya Koyanagi <tomk79@gmail.com>
 */
class bowl{

	/**
	 * Paprikaオブジェクト
	 */
	private $paprika;

	/**
	 * Picklesオブジェクト
	 * 動的なプレビュー時には `$px` が格納されます。
	 * パブリッシュされたアプリケーションの実行時には、 `$px` は利用できません。
	 * 代わりに、 `false` が格納されます。
	 */
	private $px = false;

	/** $_SERVER のメモ */
	private $SERVER_MEMO;

	/**
	 * コンテンツボウル
	 */
	private $contents_bowl = array(
		// 'main'=>'', // メインコンテンツ
		// 'head'=>'', // ヘッドセクションに追記
		// 'foot'=>''  // body要素の最後に追記
	);

	/**
	 * Constructor
	 * @param object $paprika Paprikaオブジェクト
	 * @param object $px Picklesオブジェクト
	 * @param object $SERVER_MEMO `$_SERVER` の写し
	 */
	public function __construct( $paprika, $px, $SERVER_MEMO ){
		$this->paprika = $paprika;
		$this->px = $px;
		$this->SERVER_MEMO = $SERVER_MEMO;
	}

	/**
	 * コンテンツボウルにコンテンツを入れる。
	 *
	 * ソースコードを `$paprika` オブジェクトに預けます。
	 * このメソッドから預けられたコードは、同じ `$content_name` 値 をキーにして、
	 * `$paprika->bowl()->get()` または `$paprika->bowl()->get_clean()` から引き出すことができます。
	 *
	 * この機能は、コンテンツからテーマへコンテンツを渡すために使用されます。
	 *
	 * 同じ名前(`$content_name`値)で複数回ソースを送った場合、後方に追記されます。
	 *
	 * @param string $src 入れるHTMLソース
	 * @param string $content_name ボウルの格納名。(省略時 `main`)
	 * `$paprika->bowl()->get()` で取り出す際に使用する名称です。
	 * 任意の名称が利用できます。 Paprika Framework の標準状態では、
	 * 無名(空白文字列) = `main` = メインコンテンツ
	 * が定義されています。
	 *
	 * @return bool 成功時 true、失敗時 false
	 */
	public function put( $src, $content_name = 'main' ){
		if( !is_string($content_name ?? "") ){
			return false;
		}
		if( !strlen($content_name ?? "") ){
			$content_name = 'main';
		}
		if( !array_key_exists($content_name, $this->contents_bowl) ){
			$this->contents_bowl[$content_name] = '';
		}
		$this->contents_bowl[$content_name] .= $src;
		return true;
	}

	/**
	 * コンテンツボウルのコンテンツを置き換える。
	 *
	 * ソースコードを$pxオブジェクトに預けます。
	 * `$paprika->bowl()->put()` と同じですが、複数回送信した場合に、このメソッドは追記ではなく上書きする点が異なります。
	 *
	 * @param string $src 送るHTMLソース
	 * @param string $content_name ボウルの格納名。(省略時 'main')
	 * `$paprika->bowl()->get_clean()` から取り出す際に使用する名称です。
	 * 任意の名称が利用できます。PxFWの標準状態では、無名(空白文字列) = メインコンテンツ、'head' = ヘッダー内コンテンツ の2種類が定義されています。
	 *
	 * @return bool 成功時 true、失敗時 false
	 */
	public function replace( $src, $content_name = 'main' ){
		if( !is_string($content_name ?? "") ){
			return false;
		}
		if( !strlen($content_name ?? "") ){
			$content_name = 'main';
		}
		$this->contents_bowl[$content_name] = $src;
		return true;
	}

	/**
	 * コンテンツボウルからコンテンツを取り出す。
	 *
	 * 取り出したコンテンツは、ボウルから削除されます。
	 *
	 * @param string $content_name ボウルの格納名。(省略時 `main`)
	 * @return mixed 成功時、ボウルから得られたHTMLソースを返す。失敗時、false
	 */
	public function get_clean( $content_name = 'main' ){
		if( !is_string($content_name ?? "") ){
			return false;
		}
		if( !strlen($content_name ?? "") ){
			$content_name = 'main';
		}
		if( !array_key_exists($content_name, $this->contents_bowl) ){
			return null;
		}

		$content = $this->contents_bowl[$content_name] ?? null;
		unset( $this->contents_bowl[$content_name] );// コンテンツを取り出したら、ボウル上にはなくなる。

		return $content;
	}

	/**
	 * コンテンツボウルからコンテンツの複製を取り出す。
	 *
	 * 取り出したコンテンツは、ボウル内にも残ります。
	 *
	 * @param string $content_name ボウルの格納名。(省略時 `main`)
	 * @return mixed 成功時、ボウルから得られたHTMLソースを返す。失敗時、false
	 */
	public function get( $content_name = 'main' ){
		if( !is_string($content_name ?? "") ){
			return false;
		}
		if( !strlen($content_name ?? "") ){
			$content_name = 'main';
		}
		if( !array_key_exists($content_name, $this->contents_bowl) ){
			return null;
		}

		$content = $this->contents_bowl[$content_name] ?? null;

		return $content;
	}

	/**
	 * コンテンツボウルにあるコンテンツの索引を取得する。
	 *
	 * @return array ボウルのキーの一覧
	 */
	public function get_keys(){
		$keys = array_keys( $this->contents_bowl );
		return $keys;
	}

	/**
	 * 全ボウルのコンテンツに同じ加工を施す。
	 *
	 * @param callback $func 加工処理関数
	 * @return object $bowlオブジェクト
	 */
	public function each( $func ){
		foreach( $this->get_keys() as $key ){
			$src = $this->get_clean( $key );
			$src = call_user_func( $func, $src );
			$src = $this->replace( $src, $key );
		}
		return $this;
	}

	/**
	 * テンプレートにコンテンツをバインドする
	 * @return string 完成したHTML
	 */
	public function bind_template(){
		$realpath_tpl = $this->paprika->env()->realpath_files_cache.'paprika/template';

		// -----------------------------------
		// テンプレートを生成する
		if( $this->px ){
			$_SERVER = $this->SERVER_MEMO;
			$current_page_path = $this->px->req()->get_request_file_path();
			$output_json = $this->px->internal_sub_request(
				$current_page_path.'?PX=paprika._.publish_template',
				array(
					'user_agent'=>'PicklesCrawler',
					'output'=>'json'
				)
			);
			if( !is_object($output_json) ){
				$output_json = json_decode('{}');
			}
			if( is_array($output_json->relatedlinks ?? null) ){
				foreach($output_json->relatedlinks as $url){
					$this->px->add_relatedlink($url);
				}
			}
			if( strlen($output_json->body_base64 ?? '') ){
				$tpl = base64_decode($output_json->body_base64);
				$this->paprika->fs()->mkdir_r( dirname($realpath_tpl) );
				$this->paprika->fs()->save_file( $realpath_tpl, $tpl );
			}
			$this->SERVER_MEMO = $_SERVER;

			// $pxにテンプレートファイルのパスを通知する
			$path_tpl = $this->paprika->fs()->get_relatedpath($realpath_tpl);
			$path_tpl = $this->paprika->fs()->normalize_path($this->paprika->fs()->get_realpath('/'.$path_tpl));
			$this->px->add_relatedlink($path_tpl);
		}

		// -----------------------------------
		// テンプレートを取得する
		$tpl = "";
		if( is_file($realpath_tpl) ){
			$tpl = $this->paprika->fs()->read_file( $realpath_tpl );
		}

		// -----------------------------------
		// テンプレートにHTMLをバインドする
		foreach($this->contents_bowl as $search=>$content){
			$tpl = str_replace( '{$'.$search.'}', $content, $tpl );
		}
		return $tpl;
	}

}
