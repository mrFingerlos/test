<?php defined( 'BASEPATH' ) OR exit( 'No direct script access allowed' );

/**
 *
 * @package     PyroCMS
 * @subpackage  Categories
 * @category    Module
 */
class Admin extends Admin_Controller {

    /**
     * The id of post
     * @access protected
     * @var int
     */
    protected $id = 0;

    /**
     * Array that contains the validation rules
     * @access protected
     * @var array
     */
    protected $validation_rules = array(
        array(
            'field' => 'title',
            'label' => 'lang:awards_title_label',
            'rules' => 'trim|htmlspecialchars|required|max_length[100]|callback__check_title'
        ),
        array(
            'field' => 'slug',
            'label' => 'lang:awards_slug_label',
            'rules' => 'trim|required|alpha_dot_dash|max_length[100]|callback__check_slug'
        ),
        array(
            'field' => 'on_frontpage',
            'label' => 'lang:awards_show_on_frontpage_label',
            'rules' => 'numeric'
        ),       
        array(
            'field' => 'image',
            'label' => 'lang:awards_image_label',
            'rules' => 'trim|callback__check_image'
        ),
        array(
            'field' => 'category_id',
            'label' => 'lang:awards_category_label',
            'rules' => 'trim|numeric'
        ),
        array(
            'field' => 'intro',
            'label' => 'lang:awards_intro_label',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'body',
            'label' => 'lang:awards_content_label',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'status',
            'label' => 'lang:awards_status_label',
            'rules' => 'trim|alpha'
        ),
        array(
            'field' => 'created_on',
            'label' => 'lang:awards_date_label',
            'rules' => 'trim|required'
        ),
        array(
            'field' => 'created_on_hour',
            'label' => 'lang:awards_created_hour',
            'rules' => 'trim|numeric|required'
        ),
        array(
            'field' => 'created_on_minute',
            'label' => 'lang:awards_created_minute',
            'rules' => 'trim|numeric|required'
        ),
        array(
            'field' => 'comments_enabled',
            'label' => 'lang:awards_comments_enabled_label',
            'rules' => 'trim|numeric'
        )
    );

    /**
     * The constructor
     * @access public
     * @return void
     */
    public function __construct()
    {
        parent::Admin_Controller();

        $this->load->model( 'awards_m' );
        $this->load->model( 'awards_categories_m' );
        $this->lang->load( 'awards' );
        $this->lang->load( 'categories' );

        // Date ranges for select boxes
        $this->data->hours = array_combine($hours = range(0, 23), $hours);
        $this->data->minutes = array_combine($minutes = range(0, 59), $minutes);

        $this->data->categories = array();
        if ($categories = $this->awards_categories_m->order_by( 'title' )->get_all() )
        {
            foreach ($categories as $category)
            {
                $this->data->categories[$category->id] = $category->title;
            }
        }

        // upload settings
		$this->upload_cfg['upload_path']		= FCPATH . 'uploads/awards';
		$this->upload_cfg['allowed_types'] 		= 'png|webp|jpg|jpeg|gif';
		$this->upload_cfg['max_size'] 			= '12000';
		$this->upload_cfg['remove_spaces'] 		= TRUE;
		$this->upload_cfg['overwrite']     		= TRUE;

		$this->_path = $this->upload_cfg['upload_path'] . '/';
		$this->_check_dir( $this->_path );

		@ini_set('upload_max_filesize', '12M');

        $this->template
             ->append_metadata( css( 'awards.css', 'awards' ) )
             ->set_partial( 'shortcuts', 'admin/partials/shortcuts' );
    }

    /**
     * Show all created awards posts
     * @access public
     * @return void
     */
    public function index()
    {
        //set the base/default where clause
        $base_where = array( 'show_future' => TRUE, 'status' => 'all' );

        //add post values to base_where if f_module is posted
        $base_where = $this->input->post( 'f_category' ) ? $base_where + array( 'category' => $this->input->post( 'f_category' ) ) : $base_where;

        $base_where['status'] = $this->input->post( 'f_status' ) ? $this->input->post( 'f_status' ) : $base_where['status'];

        $base_where = $this->input->post( 'f_keywords' ) ? $base_where + array( 'keywords' => $this->input->post( 'f_keywords' ) ) : $base_where;

        // Create pagination links
        $total_rows = $this->awards_m->count_by($base_where);
        $pagination = create_pagination( 'admin/awards/index', $total_rows);

        // Using this data, get the relevant results
        $awards = $this->awards_m->limit($pagination['limit'])->get_many_by($base_where);

        foreach ($awards as &$post)
        {
            $post->author = $this->ion_auth->get_user($post->author_id);
        }

        //do we need to unset the layout because the request is ajax?
        $this->input->is_ajax_request() ? $this->template->set_layout(FALSE) : '';

        $this->template
             ->title($this->module_details['name'])
             ->set_partial( 'filters', 'admin/partials/filters' )
             ->append_metadata(js( 'admin/filter.js' ) )
             ->set( 'pagination', $pagination)
             ->set( 'awards', $awards)
             ->build( 'admin/index', $this->data);
    }

    /**
     * Create new post
     * @access public
     * @return void
     */
    public function create()
    {
        $this->load->library( 'form_validation' );

        $this->form_validation->set_rules($this->validation_rules);

        if ( $this->input->post( 'created_on' ) ) {
            $created_on = strtotime(sprintf( '%s %s:%s', $this->input->post( 'created_on' ), $this->input->post( 'created_on_hour' ), $this->input->post( 'created_on_minute' ) ) );
        } else {
            $created_on = now();
        }

        if ($this->form_validation->run() ) {
            // They are trying to put this live 
            if ( $this->input->post( 'status' ) == 'live' ) {
                role_or_die( 'awards', 'put_live' );
            }
            
            $data = $this->upload->data( 'image' );

            $id = $this->awards_m->insert( 
                array(
                    'title'             => $this->input->post( 'title' ),
                   	'on_frontpage'      => $this->input->post( 'on_frontpage' ),
                    'slug'              => $this->input->post( 'slug' ),
                    'category_id'       => $this->input->post( 'category_id' ),
                    'image'             => '/uploads/awards/' . $this->image['file_name'],
                    'intro'             => $this->input->post( 'intro' ),
                    'body'              => $this->input->post( 'body' ),
                    'status'            => $this->input->post( 'status' ),
                    'created_on'        => $created_on,
                    'comments_enabled'  => $this->input->post( 'comments_enabled' ),
                    'author_id'         => $this->user->id
                )
            );

            if ($id) {
                $this->pyrocache->delete_all( 'awards_m' );
                $this->session->set_flashdata( 'success', sprintf($this->lang->line( 'awards_post_add_success' ), $this->input->post( 'title' ) ) );
            } else {
                $this->session->set_flashdata( 'error', $this->lang->line( 'awards_post_add_error' ) );
            }

            // Redirect back to the form or main page
            $this->input->post( 'btnAction' ) == 'save_exit' ? redirect( 'admin/awards' ) : redirect( 'admin/awards/edit/' . $id);
        } else {
            // Go through all the known fields and get the post values
            foreach ($this->validation_rules as $key => $field) {
                $post->$field['field'] = set_value($field['field']);
            }

            $post->created_on = $created_on;
        }

        $this->template
             ->title($this->module_details['name'], lang( 'awards_create_title' ) )
             ->append_metadata($this->load->view( 'fragments/wysiwyg', $this->data, TRUE) )
             ->append_metadata(js( 'awards_form.js', 'awards' ) )
             ->set( 'post', $post)
             ->build( 'admin/form' );
    }

    /**
     * Edit awards post
     * @access public
     * @param int $id the ID of the awards post to edit
     * @return void
     */
    public function edit( $id )
    {
        $id OR redirect( 'admin/awards' );

        $this->load->library( 'form_validation' );

        $this->form_validation->set_rules($this->validation_rules);
        
        $award = $this->awards_m->get( $id, array( 'status'=>'all' ) );
        
        if ( empty( $award) ) {
            $this->session->set_flashdata( 'error', $this->lang->line( 'award_not_exist_error' ) );
            redirect('admin/productos');
        }
         
        $post = $this->awards_m->get( $id );
        $post->author = $this->ion_auth->get_user($post->author_id);

        // If we have a useful date, use it
        if ($this->input->post( 'created_on' ) ) {
            $created_on = strtotime(sprintf( '%s %s:%s', $this->input->post( 'created_on' ), $this->input->post( 'created_on_hour' ), $this->input->post( 'created_on_minute' ) ) );
        } else {
            $created_on = $post->created_on;
        }

        $this->id = $post->id;
        
        if ($this->form_validation->run() ) {
            // They are trying to put this live
            if ($post->status != 'live' and $this->input->post( 'status' ) == 'live' ) {
                role_or_die( 'awards', 'put_live' );
            }
            
            // @TODO: Don't require image if not provided and, if so, update it.
            //        Delete the old image too.
			
			

            $author_id = empty($post->author) ? $this->user->id : $post->author_id;
	
			if ($_FILES['image']['name']){
			// we have an image name, we should change it. 

		        $result = $this->awards_m->update($id, 
		            array(
		                'title'             => $this->input->post( 'title' ),
		               	'on_frontpage'      => $this->input->post( 'on_frontpage' ),
		                'slug'              => $this->input->post( 'slug' ),
		                'category_id'       => $this->input->post( 'category_id' ),
		                'image'             => '/uploads/awards/' .$this->image['file_name'],
		                'intro'             => $this->input->post( 'intro' ),
		                'body'              => $this->input->post( 'body' ),
		                'status'            => $this->input->post( 'status' ),
		                'created_on'        => $created_on,
		                'comments_enabled'  => $this->input->post( 'comments_enabled' ),
		                'author_id'         => $author_id
		            )
		        );			
			
			} else {
			// we don't have an image name, we should not update images.
	
		        $result = $this->awards_m->update($id, 
		            array(
		                'title'             => $this->input->post( 'title' ),
		               	'on_frontpage'      => $this->input->post( 'on_frontpage' ),
		                'slug'              => $this->input->post( 'slug' ),
		                'category_id'       => $this->input->post( 'category_id' ),
		                'intro'             => $this->input->post( 'intro' ),
		                'body'              => $this->input->post( 'body' ),
		                'status'            => $this->input->post( 'status' ),
		                'created_on'        => $created_on,
		                'comments_enabled'  => $this->input->post( 'comments_enabled' ),
		                'author_id'         => $author_id
		            )
		        );			
			
			
			} // end of if 


            
            if ($result) {
                $this->session->set_flashdata(array( 'success' => sprintf($this->lang->line( 'awards_edit_success' ), $this->input->post( 'title' ) ) ) );

                // The twitter module is here, and enabled!
//              if ($this->settings->item( 'twitter_awards' ) == 1 && ($post->status != 'live' && $this->input->post( 'status' ) == 'live' ) )
//              {
//                  $url = shorten_url( 'awards/'.$date[2].'/'.str_pad($date[1], 2, '0', STR_PAD_LEFT).'/'.url_title($this->input->post( 'title' ) ) );
//                  $this->load->model( 'twitter/twitter_m' );
//                  if ( ! $this->twitter_m->update(sprintf($this->lang->line( 'awards_twitter_posted' ), $this->input->post( 'title' ), $url) ) )
//                  {
//                      $this->session->set_flashdata( 'error', lang( 'awards_twitter_error' ) . ": " . $this->twitter->last_error['error']);
//                  }
//              }
            } else {
                $this->session->set_flashdata(array( 'error' => $this->lang->line( 'awards_edit_error' ) ) );
            }

            // Redirect back to the form or main page
            $this->input->post( 'btnAction' ) == 'save_exit' ? redirect( 'admin/awards' ) : redirect( 'admin/awards/edit/' . $id);
        }

        // Go through all the known fields and get the post values
        foreach (array_keys($this->validation_rules) as $field) {
            if (isset( $_POST[$field]) ) {
                $post->$field = $this->form_validation->$field;
            }
        }

        $post->created_on = $created_on;
        
        // Load WYSIWYG editor
        $this->template
             ->title($this->module_details['name'], sprintf(lang( 'awards_edit_title' ), $post->title) )
             ->append_metadata($this->load->view( 'fragments/wysiwyg', $this->data, TRUE) )
             ->append_metadata(js( 'awards_form.js', 'awards' ) )
             ->set( 'post', $post)
             ->build( 'admin/form' );
    }

    /**
     * Preview awards post
     * @access public
     * @param int $id the ID of the awards post to preview
     * @return void
     */
    public function preview($id = 0)
    {
        $post = $this->awards_m->get($id);

        $this->template
             ->set_layout( 'modal', 'admin' )
             ->set( 'post', $post)
             ->build( 'admin/preview' );
    }

    /**
     * Helper method to determine what to do with selected items from form post
     * @access public
     * @return void
     */
    public function action()
    {
        switch ($this->input->post( 'btnAction' ) ) {
            case 'publish':
                role_or_die( 'awards', 'put_live' );
                $this->publish();
                break;
            
            case 'delete':
                role_or_die( 'awards', 'delete_live' );
                $this->delete();
                break;
            
            default:
                redirect( 'admin/awards' );
                break;
        }
    }

    /**
     * Publish awards post
     * @access public
     * @param int $id the ID of the awards post to make public
     * @return void
     */
    public function publish($id = 0)
    {
        role_or_die( 'awards', 'put_live' );

        // Publish one
        $ids = ($id) ? array($id) : $this->input->post( 'action_to' );

        if (  ! empty($ids) ) {
            // Go through the array of slugs to publish
            $post_titles = array();
             foreach ($ids as $id) {
                // Get the current page so we can grab the id too
                if ($post = $this->awards_m->get($id) ) {
                    $this->awards_m->publish($id);

                    // Wipe cache for this model, the content has changed
                    $this->pyrocache->delete( 'awards_m' );
                    $post_titles[] = $post->title;
                }
            }
        }

        // Some posts have been published
        if ( ! empty($post_titles) ) {
            // Only publishing one post
            if (count($post_titles) == 1) {
                $this->session->set_flashdata( 'success', sprintf($this->lang->line( 'awards_publish_success' ), $post_titles[0]) );
            } else { // Publishing multiple posts
                $this->session->set_flashdata( 'success', sprintf($this->lang->line( 'awards_mass_publish_success' ), implode( '", "', $post_titles) ) );
            }
        } else { // For some reason, none of them were published
            $this->session->set_flashdata( 'notice', $this->lang->line( 'awards_publish_error' ) );
        }

        redirect( 'admin/awards' );
    }

    /**
     * Delete awards post
     * @access public
     * @param int $id the ID of the awards post to delete
     * @return void
     */
    public function delete($id = 0)
    {
        // Delete one
        $ids = ($id) ? array($id) : $this->input->post( 'action_to' );

        // Go through the array of slugs to delete
        if ( ! empty($ids) ) {
            $post_titles = array();
            
            foreach ($ids as $id) {
                // Get the current page so we can grab the id too
                if ( $post = $this->awards_m->get( $id ) ) {
                    $this->awards_m->delete($id);
                    
                    // @TODO
                    // delete image too

                    // Wipe cache for this model, the content has changed
                    $this->pyrocache->delete( 'awards_m' );
                    $post_titles[] = $post->title;
                }
            }
        }

        // Some pages have been deleted
        if ( ! empty( $post_titles ) ) {
            // Only deleting one page
            if (count($post_titles) == 1) {
                $this->session->set_flashdata( 'success', sprintf($this->lang->line( 'awards_delete_success' ), $post_titles[0]) );
            } else { // Deleting multiple pages
                $this->session->set_flashdata( 'success', sprintf($this->lang->line( 'awards_mass_delete_success' ), implode( '", "', $post_titles) ) );
            }
        } else { // For some reason, none of them were deleted
            $this->session->set_flashdata( 'notice', lang( 'awards_delete_error' ) );
        }

        redirect( 'admin/awards' );
    }

    /**
     * Callback method that checks the title of an post
     * @access public
     * @param string title The Title to check
     * @return bool
     */
    public function _check_title($title = '' )
    {
        if ( ! $this->awards_m->check_exists( 'title', $title, $this->id ) ) {
            $this->form_validation->set_message( '_check_title', sprintf( lang( 'awards_already_exist_error' ), lang( 'awards_title_label' ) ) );
            return FALSE;
        }
        
        return TRUE;
    }
    
    /**
     * Callback method that checks the slug of an post
     * @access public
     * @param string slug The Slug to check
     * @return bool
     */
    public function _check_slug($slug = '' ) {
        if ( ! $this->awards_m->check_exists( 'slug', $slug, $this->id) ) {
            $this->form_validation->set_message( '_check_slug', sprintf(lang( 'awards_already_exist_error' ), lang( 'awards_slug_label' ) ) );
            return FALSE;
        }
        
        return TRUE;
    }

    /**
     * method to fetch filtered results for awards list
     * @access public
     * @return void
     */
    public function ajax_filter()
    {
        $category = $this->input->post( 'f_category' );
        $status = $this->input->post( 'f_status' );
        $keywords = $this->input->post( 'f_keywords' );

        $post_data = array();

        if ($status == 'live' OR $status == 'draft' ) {
            $post_data['status'] = $status;
        }

        if ($category != 0) {
            $post_data['category_id'] = $category;
        }

        //keywords, lets explode them out if they exist
        if ($keywords) {
            $post_data['keywords'] = $keywords;
        }
        
        $results = $this->awards_m->search($post_data);

        //set the layout to false and load the view
        $this->template
                ->set_layout(FALSE)
                ->set( 'awards', $results)
                ->build( 'admin/index' );
    }
    
    public function _check_dir( $dir )
	{
		// check directory
		$fileOK = array();
		$fdir = explode('/', $dir);
		$ddir = '';
		
		for( $i = 0; $i < count( $fdir ); $i++ ) {
			$ddir .= $fdir[$i] . '/';
			
			if ( !is_dir( $ddir ) ) {
				if (!@mkdir( $ddir, 0777 ) ) {
					$fileOK[] = 'not_ok';
				} else {
					$fileOK[] = 'ok';
				}
			} else {
				$fileOK[] = 'ok';
			}
		}

		return $fileOK;
	}
	
	public function _check_image()
	{
		$imgdata = array(
			'max_width' => 160,
			'max_height'=>151
		);

		$fconfig = $this->upload_cfg;

		$this->load->library( 'upload' );
		$this->upload->initialize( $fconfig );
		$this->_check_dir( $this->_path );
		
		// File upload error
		if ( ! $this->upload->do_upload('image') ) {

			// if no image name, then there is no Change on the image.
			// therefor We should let them pass.   		
			if (! $_FILES['image']['name']){
				return TRUE;			
			} else {
				$this->form_validation->set_message('_check_image', lang('awards_image_label'). ': ' . $this->upload->display_errors('<span>', '</span>') );
				return FALSE;
			}  
			
			
		} else { // File upload success
			$file = $this->upload->data();

			$this->load->library('image_lib');
			$filename = $file['file_name'];

			if ($file['image_width'] <> $imgdata['max_width'] OR $file['image_height'] <> $imgdata['max_height']) {
			    // resize image
				$image_cfg['source_image'] = $file['full_path'];
				$image_cfg['maintain_ratio'] = FALSE;
				$image_cfg['width'] = ( $file['image_width'] < $imgdata['max_width'] ? $file['image_width'] : $imgdata['max_width'] );
				$image_cfg['height'] = ( $file['image_height'] < $imgdata['max_height'] ? $file['image_height'] : $imgdata['max_height'] );
				$image_cfg['create_thumb'] = FALSE;
				
				$this->image_lib->initialize( $image_cfg );
				
				$img_ok = $this->image_lib->resize();
				
				unset( $image_cfg );
				$this->image_lib->clear();
			}
			
			if ( $filename <> $file['file_name'] ) {
				if ( file_exists( $file['full_path'] ) ) {
				    @unlink( $file['full_path'] );
				}
			}

			$this->image =& $file;
			return TRUE;
		}
	}
}
