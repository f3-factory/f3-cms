<?php

//! Admin back-end processor
class Admin extends Controller {

	//! HTTP route pre-processor
	function beforeroute($f3) {
		if ($f3->get('SESSION.user_id')!=$f3->get('user_id') ||
			$f3->get('SESSION.crypt')!=$f3->get('password'))
			// Invalid session
			$f3->reroute('/login');
		if ($f3->get('SESSION.lastseen')+$f3->get('expiry')*3600<time())
			// Session has expired
			$f3->reroute('/logout');
		// Update session data
		$f3->set('SESSION.lastseen',time());
		// Prepare admin menu
		$f3->set('menu',
			array(
				'/admin/pages'=>'Pages',
				'/admin/assets'=>'Assets',
				'/logout'=>'Logout'
			)
		);
	}

	//! List content pages
	function pages($f3) {
		$db=$this->db;
		// Retrieve list
		$f3->set('pages',
			$db->exec(
				'SELECT slug,title,updated FROM pages ORDER BY position;'));
		// Define HTML subtemplate
		$f3->set('inc','pages.htm');
	}

	//! Re-sequence pages
	function move($f3) {
		$db=$this->db;
		// Determine direction of re-sequencing (-1:up, +1:down)
		$vector=$f3->sign($f3->get('GET.vector'));
		// Get reference page
		$page=new DB\SQL\Mapper($db,'pages');
		$slug=($f3->get('GET.slug')?:'');
		$page->load(array('slug=?',$slug));
		if (!$page->dry()) {
			// Find page to swap positions with
			$swap=$page->findone(array('position=?+?',
				array(1=>$page->get('position'),2=>$vector)));
			// Swap positions
			$db->exec(
				array(
					'UPDATE pages '.
						'SET position=position+? WHERE slug=?;',
					'UPDATE pages '.
						'SET position=position-? WHERE slug=?;'
				),
				array(
					array(1=>$vector,2=>$slug),
					array(1=>$vector,2=>$swap->get('slug'))
				)
			);
		}
		// Redirect
		$f3->reroute('/admin/pages');
	}

	//! Display editor form
	function edit($f3) {
		// Define empty form
		$empty=array(
			'title'=>NULL,
			'contents'=>NULL,
			'updated'=>NULL,
			'commentable'=>NULL
		);
		if ($f3->exists('GET.slug')) {
			// Find matching page
			$page=new DB\SQL\Mapper($this->db,'pages');
			$page->load(array('slug=?',$f3->get('GET.slug')));
			if ($page->dry())
				// No match; new page
				$f3->set('POST',$empty);
			else
				// Existing page; prepare to edit
				$page->copyto('POST');
		}
		else
			// New page
			$f3->set('POST',$empty);
		// Define HTML subtemplate
		$f3->set('inc','editor.htm');
	}

	//! Process editor form
	function exec($f3) {
		$db=$this->db;
		$page=new DB\SQL\Mapper($db,'pages');
		// Validate submitted form
		if (!$f3->exists('POST.title') || !strlen($f3->get('POST.title')))
			$f3->set('message','Title is required');
		elseif (!$f3->exists('POST.contents') ||
			!strlen($f3->get('POST.contents')))
			$f3->set('message','Page contents cannot be blank');
		else {
			// Generate slug from title
			$slug=Web::instance()->slug($f3->get('POST.title'));
			if ($slug=='home')
				$slug='';
			if ($f3->exists('POST.erase') && $slug) {
				// Find matching page
				$page->load(array('slug=?',$slug));
				if (!$page->dry()) {
					// Get current position
					$pos=$page->get('position');
					// Delete current page and re-sequence succeeding pages
					$db->exec(
						array(
							'DELETE FROM pages WHERE slug=?',
							'UPDATE pages SET position=position-1 '.
								'WHERE position>?'
						),
						array(
							array(1=>$slug),
							array(1=>$pos)
						)
					);
					// Erase all comments related to the page
					$comment=new DB\SQL\Mapper($db,'comments');
					$comment->erase(array('slug=?',$slug));
				}
			}
			elseif ($f3->exists('POST.save')) {
				// Find matching page
				$page->load(array('slug=?',$slug));
				if ($page->dry()) {
					// Determine highest position in sequence
					$query=current(
						$db->exec(
							'SELECT MAX(position) AS maxpos FROM pages;'));
					$page->set('position',$query['maxpos']+1);
				}
				// Replace data
				$page->copyfrom('POST');
				if (!$f3->exists('POST.commentable'))
					$page->set('commentable',NULL);
				$page->set('slug',$slug);
				$page->set('updated',time());
				$page->save();
			}
			// Redirect
			$f3->reroute('/admin/pages');
		}
		// Define HTML subtemplate
		$f3->set('inc','editor.htm');
	}

	//! List assets
	function assets($f3) {
		// Build list from files in the uploads folder
		$assets=array();
		foreach (glob($f3->get('UPLOADS').'*') as $file)
			$assets[]=array(
				'name'=>basename($file),
				'posted'=>filemtime($file)
			);
		$f3->set('assets',$assets);
		// Define HTML subtemplate
		$f3->set('inc','assets.htm');
	}

	//! Process asset upload
	function upload($f3) {
		$db=$this->db;
		if (Web::instance()->receive(
			function($file) use($f3) {
				// Permit only MIME types specified in config file
				$allowed=$f3->get('allowed');
				return is_array($allowed) && in_array($file['type'],$allowed);
			}))
			// Redirect
			$f3->reroute('/admin/assets');
		// Something's not right with this uploaded file
		$f3->set('message','Upload failed');
		$this->assets($f3);
	}

}
