<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @package 		PyroCMS
 * @subpackage 		Category Menu Widget
 * @author			Guillermo Duran
 * 
 * Show a list of awards on frontpage.
 */

class Widget_Awards_On_Frontpage extends Widgets
{
	public $title		= array(
		'en' => 'Awards On FrontPage',
		'es' => 'Premios en Página principal',
		'pt' => 'Categorias do Awards',
	);
	public $description	= array(
		'en' => 'Show a list of awards on the front page',
		'es' => 'Muestra una Lista de Awards en la Página principal',
		'pt' => 'Mostra uma lista de navegação com do Awards',
	);
	public $author		= 'Guillermo Duran';
	public $website		= 'http://xpc.mx/';
	public $version		= '1.0';
	
	public function run()
	{
		$this->load->model('awards/awards_m');
		
		$awards_frontpage = $this->awards_m->order_by('title')->get_all_frontpage();
		
		return array('awards_frontpage' => $awards_frontpage);
	}	
}