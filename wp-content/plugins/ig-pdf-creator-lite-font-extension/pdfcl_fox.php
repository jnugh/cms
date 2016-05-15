<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/*
Plugin Name: pdf_creator_lite_font_extension
Version: 1.2
Author: Blanz
Depends: pdf-creator-lite, PDF Creator LITE
*/
require_once('fox_constants.php');

/**
 *
 * @param $pdf
 * @return bool
 */
function set_fonts($pdf){
	$font = $pdf->text_font;
	$path = __FILE__;				// --> ...wordpress\wp-content\plugins\pdf-creator-lite-font-extension\pdfcl_fox.php
	$path = str_replace(fox_constants::FOX_DELIMITER_WINDOWS,fox_constants::FOX_DELIMITER,$path);
	$path = dirname($path); 		// --> ...wordpress/wp-content/plugins/pdf-creator-lite-font-extension
	install_fonts($path,$pdf);
	$pdf->SetFont( $font, '', 11, '', true, true );
	$pdf->SetRTL(false);
	if(ICL_LANGUAGE_CODE!==null){
		if(ICL_LANGUAGE_CODE==fox_constants::FOX_LANGUAGE_ARABIC || ICL_LANGUAGE_CODE==fox_constants::FOX_LANGUAGE_PERSIAN){
			$pdf->SetRTL(true);
		}
	}
	return $pdf;
}

/**
 * Add fonts to tcpdf
 * @param $path: path where the font has to be installed
 * @param $pdf: object the font is added to
 */
function install_fonts($path,$pdf){
	$worked = false;
	$path = $path.fox_constants::FOX_DELIMITER.fox_constants::FOX_FONTS.fox_constants::FOX_DELIMITER;
	foreach (glob($path."*".fox_constants::FOX_EXT_TTF) as $file) {
		if(has_font($file)){
			if(file_exists($file)){
				$worked = $pdf->addTTFfont($file);
				//something went wrong with the font --> add ERROR file extension
				if($worked === false){
					$filename = basename($file);
					$new_filename = $filename.fox_constants::FOX_EXT_ERROR;
					$filename = $path.$filename;
					$new_filename = $path.$new_filename;
					rename($filename,$new_filename);
				}
			}						
		}
		//else font already added --> do nothing
	}
}

/**
 * check if tcpdf has the font installed already
 * e.G.:
 * input: /wordpress/wp-content/plugins/pdf-creator-lite-font-extension/fonts/dejavusans.ttf
 * returns true if font is not exisiting
 *
 * @param $file: the font which has to be checked regarding existance
 * @return bool
 */
function has_font($file){
	$path = dirname($file); 		// --> .../wordpress/wp-content/plugins/pdf-creator-lite-font-extension/fonts
	$filename = basename($file);	// --> e.g.: dejavusans.ttf
	$filename = str_replace(fox_constants::FOX_EXT_TTF,'',$filename); // --> e.g.: defavusans
	$filename = strtolower($filename);
	$path = dirname($path); 		// --> .../wordpress/wp-content/plugins/pdf-creator-lite-font-extension
	$path = dirname($path);  		// --> .../wordpress/wp-content/plugins
	$path .= fox_constants::FOX_DELIMITER.fox_constants::FOX_FONT_DIRECTORY; // --> .../wordpress/wp-content/plugins/pdf-creator-lite/tcpdf/fonts/

	//TODO TCPDF might modify the filename. E.g.: by removing '-'
	if(file_exists($path.$filename.fox_constants::FOX_EXT_CTG_Z) &&
		file_exists($path.$filename.fox_constants::FOX_EXT_Z) &&
		file_exists($path.$filename.fox_constants::FOX_EXT_PHP)){
			return false;
	}
	return true;
}

/**
 * For each font in the plugins fonts-folder, add select boxes to the adminpage.php
 */
function set_select(){

	$first = '';
	$selection = '';
	$path = __FILE__;
	$path = str_replace(fox_constants::FOX_DELIMITER_WINDOWS,fox_constants::FOX_DELIMITER,$path);
	$path = dirname($path);
	add_language();
	$path = $path.fox_constants::FOX_DELIMITER.fox_constants::FOX_FONTS.fox_constants::FOX_DELIMITER; //--> ".../wordpress/wp-content/plugins/pdf-creator-lite-font-extension/fonts/"
	foreach (glob($path."*".fox_constants::FOX_EXT_TTF) as $file) {
		$filename = basename($file,fox_constants::FOX_EXT_TTF);		//e.G.: basename("/etc/sudoers.d", ".d"). --> sudoers
		$filename = strtolower($filename);
		if($filename == fox_constants::FOX_DEJAVUSANS){				//for persian we use dejavusans
			if(ICL_LANGUAGE_CODE == fox_constants::FOX_LANGUAGE_PERSIAN){
				$first = '<option value="'.$filename.'" selected>'.$filename.'</option>';
			}
			else{
				$selection.= '<option value="'.$filename.'">'.$filename.'</option>';
			}
		}
		else if($filename == fox_constants::FOX_AEFURAT){				//for arabic we use aefurat
			if(ICL_LANGUAGE_CODE == fox_constants::FOX_LANGUAGE_ARABIC){
				$first = '<option value="'.$filename.'" selected>'.$filename.'</option>';
			}
			else{
				$selection.= '<option value="'.$filename.'">'.$filename.'</option>';
			}
		}
		else{
			$selection.= '<option value="'.$filename.'">'.$filename.'</option>';
		}
	}	
	echo($first.$selection);
}

function add_language(){

	$args = array(
			'sort_order' => 'ASC',
			'sort_column' => 'menu_order',
			'hierarchical' => 1,
			'exclude' => '',
			'include' => '',
			'post_type' => 'page',
			'post_status' => 'publish'
	);
	$pages = get_pages( $args );

	echo '<script type="text/javascript">';
	//1. After an Empty Div with the same ID
	echo 'jQuery("#selectPagesDiv").attr("id","help_id");';
	echo 'jQuery("#help_id").after("<div id=\"selectPagesDiv\"><\/div>");';
	//2. Append heading
	echo 'jQuery("#selectPagesDiv").append("<h3>Select your pages</h3>");';
	//3. Append the Check-All Box
	echo 'jQuery("#selectPagesDiv").append(\'<div style="height:30px; border-bottom:1px solid #CCCCCC;"><input value="true" id="checkAll" name="checkAll" checked="checked" type="checkbox"><label for="checkAll">Uncheck / Check All</label></div>\<br></br>\');';
	//4. Create Table
	echo 'jQuery("#selectPagesDiv").append(\'<table id="fox_table_languages" style="width:100%; border: 1px solid #FDDA0E; border-collapse: separate; border-spacing: 5px;"></table>\');';
		// width first column - check box
	$cwidth=158;
	echo 'jQuery("#fox_table_languages").append(\'<colgroup id="fox_column_width"></colgroup>\');';
	echo 'jQuery("#fox_column_width").append(\'<col width="10">\');';
	echo 'jQuery("#fox_column_width").append(\'<col width="'.$cwidth.'">\');';
	echo 'jQuery("#fox_column_width").append(\'<col width="'.$cwidth.'">\');';
	echo 'jQuery("#fox_column_width").append(\'<col width="'.$cwidth.'">\');';
	echo 'jQuery("#fox_column_width").append(\'<col width="'.$cwidth.'">\');';
	echo 'jQuery("#fox_column_width").append(\'<col width="'.$cwidth.'">\');';
	echo 'jQuery("#fox_table_languages").append(\'<tr><th></th><th>German</th><th>English</th><th>French</th><th>Arabian</th><th>Persian</th></tr>\');';
	//5. remove Original Element
	echo 'jQuery("#help_id").remove();';
	foreach ($pages as $page)
	{
		$original_ID_DE = icl_object_id( $page->ID,'post',false, 'de' );
		$original_title_de = get_the_title( $original_ID_DE );
		$original_ID_EN = icl_object_id( $page->ID,'post',false, 'en' );
		$original_title_en = get_the_title( $original_ID_EN );
		$original_ID_FR = icl_object_id( $page->ID,'post',false, 'fr' );
		$original_title_fr = get_the_title( $original_ID_FR );
		$original_ID_AR = icl_object_id( $page->ID,'post',false, 'ar' );
		$original_title_ar = get_the_title( $original_ID_AR );
		$original_ID_FA = icl_object_id( $page->ID,'post',false, 'fa' );
		$original_title_fa = get_the_title( $original_ID_FA );
		echo 'jQuery("#fox_table_languages").append(\'<tr>'.
				'<td style="padding: 0 5px 0 5px;"><input name="checkerPage['.$page->ID.']" class="checkerPage" value="'.$page->ID.'" checked="checked" id="page'.$page->ID.'" type="checkbox"></td>'.
				'<td style="padding: 0 5px 0 5px;"><label for="page'.$page->ID.'"><font color=\"blue\"><i>'.$original_title_de.'</i></font></label></label></td>'.
				'<td style="padding: 0 5px 0 5px;"><label for="page'.$page->ID.'"><font color=\"brown\"><i>'.$original_title_en.'</i></font></label></label></td>'.
				'<td style="padding: 0 5px 0 5px;"><label for="page'.$page->ID.'"><font color=\"red\"><i>'.$original_title_fr.'</i></font></label></label></td>'.
				'<td style="padding: 0 5px 0 5px;"><label for="page'.$page->ID.'"><font color=\"orange\"><i>'.$original_title_ar.'</i></font></label></label></td>'.
				'<td style="padding: 0 5px 0 5px;"><label for="page'.$page->ID.'"><font color=\"green\"><i>'.$original_title_fa.'</i></font></label></label></td>'.
		'</tr>\');';
	}
	echo 'jQuery("tr:even").css("background-color", "#E5E4E2");';

	foreach ($pages as $page)
	{
		$original_ID_DE = icl_object_id( $page->ID,'post',false, 'de' );
		$original_title_de = get_the_title( $original_ID_DE );
		$original_ID_EN = icl_object_id( $page->ID,'post',false, 'en' );
		$original_title_en = get_the_title( $original_ID_EN );
		echo 'jQuery(\'#page'.$page->ID.'\').next("label").append(" -- <font color=\"blue\"><i>'.$original_title_de.'</i></font>");';
		echo 'jQuery(\'#page'.$page->ID.'\').next("label").append(" -- <font color=\"brown\"><i>'.$original_title_en.'</i></font>");';
	}
	echo '</script>';

}

/****************************************/
/*         Filters and Actions          */
/****************************************/ 

add_filter('fox_modify_pdf','set_fonts');

add_action('fox_add_fonts','set_select');