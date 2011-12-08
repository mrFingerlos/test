<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @package 		PyroCMS
 * @subpackage 		Category Menu Widget
 * @author			Stephen Cozart
 * 
 * Show a list of awards categories.
 */

class Widget_Awards_categories extends Widgets
{
	public $title		= array(
		'en' => 'Awards Categories',
		'pt' => 'Categorias do Awards',
		'ru' => 'Категории Блога',
	);
	public $description	= array(
		'en' => 'Show a list of awards categories',
		'pt' => 'Mostra uma lista de navegação com as categorias do Awards',
		'ru' => 'Выводит список категорий блога',
	);
	public $author		= 'Stephen Cozart';
	public $website		= 'http://github.com/clip/';
	public $version		= '1.0';
	
	public function run()
	{
		$this->load->model('awards/awards_categories_m');
		
		$categories = $this->awards_categories_m->order_by('title')->get_all();
		
		return array('categories' => $categories);
	}	
}