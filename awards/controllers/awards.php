<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Awards extends Public_Controller
{
	public $limit = 5; // TODO: PS - Make me a settings option

	public function __construct()
	{
		parent::Public_Controller();
		$this->load->model('awards_m');
		$this->load->model('awards_categories_m');
		$this->load->model('comments/comments_m');
		$this->load->helper('text');
		$this->lang->load('awards');
	}

	// awards/page/x also routes here
	public function index()
	{
		$this->data->pagination = create_pagination('awards/page', $this->awards_m->count_by(array('status' => 'live')), NULL, 3);
		$this->data->awards = $this->awards_m->limit($this->data->pagination['limit'])->get_many_by(array('status' => 'live'));

		// Set meta description based on post titles
		$meta = $this->_posts_metadata($this->data->awards);

		$this->template
			->title($this->module_details['name'])
			->set_breadcrumb( lang('awards_awards_title'))
			->set_metadata('description', $meta['description'])
			->set_metadata('keywords', $meta['keywords'])
			->build('index', $this->data);
	}

	public function category($slug = '')
	{
		$slug OR redirect('awards');

		// Get category data
		$category = $this->awards_categories_m->get_by('slug', $slug) OR show_404();

		// Count total awards posts and work out how many pages exist
		$pagination = create_pagination('awards/category/'.$slug, $this->awards_m->count_by(array(
			'category'=>$slug,
			'status' => 'live'
		)), NULL, 4);

		// Get the current page of awards posts
		$awards = $this->awards_m->limit($pagination['limit'])->get_many_by(array(
			'category'=> $slug,
			'status' => 'live'
		));

		// Set meta description based on post titles
		$meta = $this->_posts_metadata($awards);

		// Build the page
		$this->template->title($this->module_details['name'], $category->title )
			->set_metadata('description', $category->title.'. '.$meta['description'] )
			->set_metadata('keywords', $category->title )
			->set_breadcrumb( lang('awards_awards_title'), 'awards')
			->set_breadcrumb( $category->title )
			->set('awards', $awards)
			->set('category', $category)
			->set('pagination', $pagination)
			->build('category', $this->data );
	}

	public function archive($year = NULL, $month = '01')
	{
		$year OR $year = date('Y');
		$month_date = new DateTime($year.'-'.$month.'-01');
		$this->data->pagination = create_pagination('awards/archive/'.$year.'/'.$month, $this->awards_m->count_by(array('year'=>$year,'month'=>$month)), NULL, 5);
		$this->data->awards = $this->awards_m->limit($this->data->pagination['limit'])->get_many_by(array('year'=> $year,'month'=> $month));
		$this->data->month_year = format_date($month_date->format('U'), lang('awards_archive_date_format'));

		// Set meta description based on post titles
		$meta = $this->_posts_metadata($this->data->awards);

		$this->template->title( $this->data->month_year, $this->lang->line('awards_archive_title'), $this->lang->line('awards_awards_title'))
			->set_metadata('description', $this->data->month_year.'. '.$meta['description'])
			->set_metadata('keywords', $this->data->month_year.', '.$meta['keywords'])
			->set_breadcrumb($this->lang->line('awards_awards_title'), 'awards')
			->set_breadcrumb($this->lang->line('awards_archive_title').': '.format_date($month_date->format('U'), lang('awards_archive_date_format')))
			->build('archive', $this->data);
	}

	// Public: View an post
	public function view($slug = '')
	{
		if ( ! $slug or ! $post = $this->awards_m->get_by('slug', $slug))
		{
			redirect('awards');
		}

		if ($post->status != 'live' && ! $this->ion_auth->is_admin())
		{
			redirect('awards');
		}

		$post->author = $this->ion_auth->get_user($post->author_id);

		// IF this post uses a category, grab it
		if ($post->category_id && ($category = $this->awards_categories_m->get($post->category_id)))
		{
			$post->category = $category;
		}

		// Set some defaults
		else
		{
			$post->category->id		= 0;
			$post->category->slug	= '';
			$post->category->title	= '';
		}

		$this->session->set_flashdata(array('referrer' => $this->uri->uri_string));

		$this->template->title($post->title, lang('awards_awards_title'))
			->set_metadata('description', $post->intro)
			->set_metadata('keywords', $post->category->title.' '.$post->title)
			->set_breadcrumb(lang('awards_awards_title'), 'awards');

		if ($post->category->id > 0)
		{
			$this->template->set_breadcrumb($post->category->title, 'awards/category/'.$post->category->slug);
		}

		$this->template
			->set_breadcrumb($post->title)
			->set('post', $post)
			->build('view', $this->data);
	}

	// Private methods not used for display
	private function _posts_metadata(&$posts = array())
	{
		$keywords = array();
		$description = array();

		// Loop through posts and use titles for meta description
		if(!empty($posts))
		{
			foreach($posts as &$post)
			{
				if($post->category_title)
				{
					$keywords[$post->category_id] = $post->category_title .', '. $post->category_slug;
				}
				$description[] = $post->title;
			}
		}

		return array(
			'keywords' => implode(', ', $keywords),
			'description' => implode(', ', $description)
		);
	}
}
