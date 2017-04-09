<?php
/**
 * Pickles 2 - Paprika Framework
 */
namespace tomk79\pickles2\paprikaFramework2;

/**
 * paprika.php
 */
class paprika{

	/** config object */
	private $conf;

	/** Pickles Framework 2 Object */
	private $px;

	/**
	 * constructor
	 * @param object $conf Paprika Config
	 * @param object $px Picklesオブジェクト (プレビュー時は `$px` オブジェクト、パブリッシュ後には `false` を受け取ります)
	 */
	public function __construct( $conf, $px ){
		$this->conf = $conf;
		$this->px = $px; // パブリッシュ後には `false` を受け取ります。

		// デフォルトのHTTPレスポンスヘッダー
		@header('Content-type: text/html');
	}

	/**
	 * 設定を取得する
	 * @return object 設定オブジェクト
	 */
	public function conf(){
		return $this->conf;
	}

	/**
	 * テンプレートにコンテンツをバインドする
	 * @return string 完成したHTML
	 */
	public function bind_template($contents, $path_tpl){
		// -----------------------------------
		// テンプレートを取得する
		$tpl = '';
		if( $this->px ){
			// is preview
			// $px が存在する場合は、プレビュー環境だと判断。
			$tpl = $this->px->internal_sub_request($path_tpl);
		}else{
			// is finalized
			// .px_execute.php が存在しなければ、パブリッシュ後の実行であると判断。
			$tpl = file_get_contents( $_SERVER['DOCUMENT_ROOT'].$this->conf->path_controot.$path_tpl );
		}

		// -----------------------------------
		// テンプレートにHTMLをバインドする
		foreach($contents as $search=>$content){
			$tpl = str_replace( $search, $content, $tpl );
		}

		return $tpl;
	}

}
