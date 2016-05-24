<?php

/*/

----- Company Site
ironarm.co
ironarm.co/static
ironarm.co/static/edit
ironarm.co/static/delete

ironarm.co/parking/features
ironarm.co/parking/about
ironarm.co/parking/news/
ironarm.co/parking/news/news-item
ironarm.co/parking/news/news-item/delete-edit
ironarm.co/parking/app/desktop/
ironarm.co/parking/app/mobile/

//*/

$routing = array(
//  URL ----------------------- Module
	'blog/new'                      => 'Blog->newPost',
	//'blog/rss.xml'                  => 'Blog->getFeed',
	
	'blog/'                         => 'Blog->index',
	'blog/page/'                    => 'Blog->index',
	'blog/page/:int'                => 'Blog->index',
	'blog/rss'                      => 'Blog->rss',
	'blog/:any/edit'                => 'Blog->editPost',
	'blog/:any/delete'              => 'Blog->deletePost',
	'blog/:any'                     => 'Blog->getPost',
	
	'about/'                        => 'About->index',
	'about/new'                     => 'About->newPost',
	'about/:any'                    => 'About->getPost',
	
	//'user/new'                      => 'Users->newUser',
	//'users/'                        => 'Users->getList',
	//'user/:any'                     => 'Users->getUser',
	
	''                              => 'Home->index',
	
	//'new'                           => 'Pages->newPage',
	'login'                         => 'Pages->login',
	'logout'                         => 'Pages->logout',
	//'contact'                       => 'Pages->contact',
	//':any/edit'                     => 'Pages->edit',
	//':any/delete'                   => 'Pages->delete',
	//':any'                          => 'Pages->getPage'
);

?>