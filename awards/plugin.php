<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Awards Plugin
 *
 * Create lists of posts
 *
 * @package		PyroCMS
 * @author		PyroCMS Dev Team
 * @copyright	Copyright (c) 2008 - 2011, PyroCMS
 *
 */
class Plugin_Awards extends Plugin
{
	/**
	 * Awards List
	 *
	 * Creates a list of awards posts
	 *
	 * Usage:
	 * {pyro:awards:posts order-by="title" limit="5"}
	 *	<h2>{pyro:title}</h2>
	 *	{pyro:body}
	 * {/pyro:awards:posts}
	 *
	 * @param	array
	 * @return	array
	 */
	public function posts()
	{
		$limit		= $this->attribute('limit', 10);
		$category	= $this->attribute('category');
		$order_by 	= $this->attribute('order-by', 'created_on');
													//deprecated
		$order_dir	= $this->attribute('order-dir', $this->attribute('order', 'ASC'));

		if ($category)
		{
			$this->db->where('c.' . (is_numeric($category) ? 'id' : 'slug'), $category);
		}

		$posts = $this->db
			->select('awards.*')
			->select('c.title as category_title, c.slug as category_slug')
			->select('p.display_name as author_name')
			->where('status', 'live')
			->where('created_on <=', now())
			->join('awards_categories c', 'awards.category_id = c.id', 'left')
			->join('profiles p', 'awards.author_id = p.user_id')
			->order_by('awards.' . $order_by, $order_dir)
			->limit($limit)
			->get('awards')
			->result();

		foreach ($posts as &$post)
		{
			$post->url = site_url('awards/'.date('Y', $post->created_on).'/'.date('m', $post->created_on).'/'.$post->slug);
		}
		
		return $posts;
	}
}

/* End of file plugin.php */
