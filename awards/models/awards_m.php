<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Awards_m extends MY_Model {

	protected $_table = 'awards';

	function get_all()
	{
		$this->db->select('awards.*, awards_categories.title AS category_title, awards_categories.slug AS category_slug');
		$this->db->join('awards_categories', 'awards.category_id = awards_categories.id', 'left');

		$this->db->order_by('created_on', 'DESC');

		return $this->db->get('awards')->result();
	}
	
	function get_all_frontpage()
	{
		$this->db->select('awards.*');
		$this->db->order_by('created_on', 'DESC');
		$this->db->where('awards.on_frontpage',1);
		return $this->db->get('awards')->result();
	}

	function get($id)
	{
		$this->db->where(array('id' => $id));
		return $this->db->get('awards')->row();
	}

	function get_many_by($params = array())
	{
		$this->load->helper('date');

		if (!empty($params['category']))
		{
			if (is_numeric($params['category']))
				$this->db->where('awards_categories.id', $params['category']);
			else
				$this->db->where('awards_categories.slug', $params['category']);
		}

		if (!empty($params['month']))
		{
			$this->db->where('MONTH(FROM_UNIXTIME(created_on))', $params['month']);
		}

		if (!empty($params['year']))
		{
			$this->db->where('YEAR(FROM_UNIXTIME(created_on))', $params['year']);
		}

		// Is a status set?
		if (!empty($params['status']))
		{
			// If it's all, then show whatever the status
			if ($params['status'] != 'all')
			{
				// Otherwise, show only the specific status
				$this->db->where('status', $params['status']);
			}
		}

		// Nothing mentioned, show live only (general frontend stuff)
		else
		{
			$this->db->where('status', 'live');
		}

		// By default, dont show future posts
		if (!isset($params['show_future']) || (isset($params['show_future']) && $params['show_future'] == FALSE))
		{
			$this->db->where('created_on <=', now());
		}

		// Limit the results based on 1 number or 2 (2nd is offset)
		if (isset($params['limit']) && is_array($params['limit']))
			$this->db->limit($params['limit'][0], $params['limit'][1]);
		elseif (isset($params['limit']))
			$this->db->limit($params['limit']);

		return $this->get_all();
	}

	function count_by($params = array())
	{
		$this->db->join('awards_categories', 'awards.category_id = awards_categories.id', 'left');

		if (!empty($params['category']))
		{
			if (is_numeric($params['category']))
				$this->db->where('awards_categories.id', $params['category']);
			else
				$this->db->where('awards_categories.slug', $params['category']);
		}

		if (!empty($params['month']))
		{
			$this->db->where('MONTH(FROM_UNIXTIME(created_on))', $params['month']);
		}

		if (!empty($params['year']))
		{
			$this->db->where('YEAR(FROM_UNIXTIME(created_on))', $params['year']);
		}

		// Is a status set?
		if (!empty($params['status']))
		{
			// If it's all, then show whatever the status
			if ($params['status'] != 'all')
			{
				// Otherwise, show only the specific status
				$this->db->where('status', $params['status']);
			}
		}

		// Nothing mentioned, show live only (general frontend stuff)
		else
		{
			$this->db->where('status', 'live');
		}

		return $this->db->count_all_results('awards');
	}

	function update($id, $input)
	{
		$input['updated_on'] = now();

		return parent::update($id, $input);
	}

	function publish($id = 0)
	{
		return parent::update($id, array('status' => 'live'));
	}

	// -- Archive ---------------------------------------------

	function get_archive_months()
	{
		$this->db->select('UNIX_TIMESTAMP(DATE_FORMAT(FROM_UNIXTIME(t1.created_on), "%Y-%m-02")) AS `date`', FALSE);
		$this->db->from('awards t1');
		$this->db->distinct();
		$this->db->select('(SELECT count(id) FROM ' . $this->db->dbprefix('awards') . ' t2
							WHERE MONTH(FROM_UNIXTIME(t1.created_on)) = MONTH(FROM_UNIXTIME(t2.created_on))
								AND YEAR(FROM_UNIXTIME(t1.created_on)) = YEAR(FROM_UNIXTIME(t2.created_on))
								AND status = "live"
								AND created_on <= ' . now() . '
						   ) as post_count');

		$this->db->where('status', 'live');
		$this->db->where('created_on <=', now());
		$this->db->having('post_count >', 0);
		$this->db->order_by('t1.created_on DESC');
		$query = $this->db->get();

		return $query->result();
	}

	// DIRTY frontend functions. Move to views
	function get_awards_fragment($params = array())
	{
		$this->load->helper('date');

		$this->db->where('status', 'live');
		$this->db->where('created_on <=', now());

		$string = '';
		$this->db->order_by('created_on', 'DESC');
		$this->db->limit(5);
		$query = $this->db->get('awards');
		if ($query->num_rows() > 0)
		{
			$this->load->helper('text');
			foreach ($query->result() as $awards)
			{
				$string .= '<p>' . anchor('awards/' . date('Y/m') . '/' . $awards->slug, $awards->title) . '<br />' . strip_tags($awards->intro) . '</p>';
			}
		}
		return $string;
	}

	function check_exists($field, $value = '', $id = 0)
	{
		if (is_array($field))
		{
			$params = $field;
			$id = $value;
		}
		else
		{
			$params[$field] = $value;
		}
		$params['id !='] = (int) $id;

		return parent::count_by($params) == 0;
	}

	/**
	 * Searches awards posts based on supplied data array
	 * @param $data array
	 * @return array
	 */
	public function search($data = array())
	{
		if (array_key_exists('category_id', $data))
		{
			$this->db->where('category_id', $data['category_id']);
		}

		if (array_key_exists('status', $data))
		{
			$this->db->where('status', $data['status']);
		}

		if (array_key_exists('keywords', $data))
		{
			$matches = array();
			if (strstr($data['keywords'], '%'))
			{
				preg_match_all('/%.*?%/i', $data['keywords'], $matches);
			}

			if (!empty($matches[0]))
			{
				foreach ($matches[0] as $match)
				{
					$phrases[] = str_replace('%', '', $match);
				}
			}
			else
			{
				$temp_phrases = explode(' ', $data['keywords']);
				foreach ($temp_phrases as $phrase)
				{
					$phrases[] = str_replace('%', '', $phrase);
				}
			}

			$counter = 0;
			foreach ($phrases as $phrase)
			{
				if ($counter == 0)
				{
					$this->db->like('awards.title', $phrase);
				}
				else
				{
					$this->db->or_like('awards.title', $phrase);
				}

				$this->db->or_like('awards.body', $phrase);
				$this->db->or_like('awards.intro', $phrase);
				$counter++;
			}
		}
		return $this->get_all();
	}

    // Images
    function insertImage( $image, $table )
	{
		$image['session_id'] = $this->user->id;
		
		if ( $this->db->insert( $this->_image_table[$table], $image ) ) {
			return $this->db->insert_id();
		} else {
			return FALSE;
		}
	}
	
	function deleteImage( $imageID, $table )
	{		
		$this->db->where( 'id', $imageID );
		return $this->db->delete( $this->_image_table[$table] );
	}
	
	function updateImage( $id, $image, $table )
	{
		$this->db->where( 'id', $id );
		return $this->db->update( $this->_image_table[$table], $input );
	}
	
	function getImage($id, $table)
	{
		if ( is_array( $id ) ) {
			$this->db->where_in( 'id', $id );
		} else {
			$this->db->where( 'id', $id );
		}
		
		return $this->db->get($this->_image_table[$table])->result();
	}
}
