<?php 
/**
 * Essa biblioteca de funções só é funcional dentro do arquivo "functions.php" do 
 * Wordpress.
 * 
 * Para um melhor uso, é recomendado utilizar o autoload do composer.
 */

function pLink($post) {
	return get_permalink($post);
}

/**
 * Retorna o nome da categoria pelo ID da mesma
 * @param  int $catId ID da categoria
 * @return string        Nome da categoria
 */
function catName($catId) {
    return get_cat_name($catId);
}

/**
 * Retorna a URL da imagem de destaque do post
 * @param  WP_Post $post 
 * @return string       Url da imagem
 */
function thumb($post) {
    return wp_get_attachment_url(get_post_thumbnail_id($post));
}

/**
 * Atalho para a função de obtenção de URL do tema
 * @return [type] [description]
 */
function tUrl() {
	return get_bloginfo('template_url');
}

function excerpt($string, $maximumSize) {
  if(strlen($string) > $maximumSize) {
    $ending = '...';
  }else{
    $ending = '';
  }

  $parts = preg_split('/([\s\n\r]+)/', $string, null, PREG_SPLIT_DELIM_CAPTURE);
  $parts_count = count($parts);

  $length = 0;
  $last_part = 0;
  for (; $last_part < $parts_count; ++$last_part) {
    $length += strlen($parts[$last_part]);
    if ($length > $maximumSize) { break; }
  }

  return implode(array_slice($parts, 0, $last_part)) . $ending;
}

function sUrl() {
	return site_url();
}

function adminAjax($action) {
    return sUrl() . '/wp-admin/admin-ajax.php?action=' .  $action;
}

function pDate($post, $format = 'd/m/Y') {
    return get_the_date($format, $post);
}

function pExcerpt($post) {
    $excerpt = ( $post->post_excerpt ) ? $post->post_excerpt : $post->post_content;
    $excerpt = strip_tags(html_entity_decode($excerpt));
    return $excerpt;
}

function pCategories($post) {
    return wp_get_post_categories($post->ID);
}

function pFirstCategory($post) {
    return pCategories($post)[0];
}


function getPagePermalinkByTitle($title) {
    return get_permalink(get_page_by_title( $title ));
}

function getPostsByType($type = 'post', $count = 4, $otherParams = []) {
  $args = [
    'post_type' => $type,
    'post_status' => 'publish',
    'posts_per_page' => $count
  ];

  $args = array_merge($args, $otherParams);

  $query = new WP_Query($args);
  $posts = $query->get_posts();
  return $posts;
}

function getCategories() {

    $args = array(
        'type'                     => 'post',
        'child_of'                 => 0,
        'parent'                   => '',
        'orderby'                  => 'name',
        'order'                    => 'ASC',
        'hide_empty'               => 1,
        'hierarchical'             => 1,
        'exclude'                  => '',
        'include'                  => '',
        'number'                   => '',
        'taxonomy'                 => 'category',
        'pad_counts'               => false 
    );
    $categories = get_categories($args);
    die(Request::toJson($categories));
}

function buildArgs($ppp = 2, array $merge = []) {
    $page = Request::get('paged');
    $page = ($page === '') ? 1 : $page;
    $args = [
      'post_type' => 'post',
      'post_status' => 'publish',
      'posts_per_page' => $ppp,
      'paged' => $page,
    ];

    $args = array_merge($args, $merge);

    $category = Request::get('category');
    $category = ($category === '') ? false : $category;
    
    if($category) {
        $catArgs = [
            'cat' => $category
        ];
        $args = array_merge($catArgs, $args);
    }

    $date = Request::get('date');
    $date = ($date === '') ? false : explode('-', $date);
    $dateArgs = [];
    if($date) {
        $dateArgs = [
            'date_query' => [
                [
                    'year' => $date[0],
                    'month' => $date[1]
                ]
            ]
        ];
        $args = array_merge($dateArgs, $args);
    }

    $s = Request::get('s');
    $s = ($s === '') ? false : $s;
    if($s) {
      $args = array_merge(['s' => $s], $args);
    }

    $private = Request::get('private');
    $private = ($private === '') ? false : $private;
    if($private) {
      if(!isLoggedIn()) {
        die(__('You must be logged in'));
      }
      $args = array_merge($args, ['post_status' => 'private']);
    }

    return $args;
}


function getPosts($ppp = 2, $json = true, $unstoppable = false) {
    if(!Request::isGet()) {
        die(Request::error("Method not allowed"));
    }

    $ppp = (empty($ppp)) ? 2 : $ppp;

    $query = new WP_Query(buildArgs($ppp));
    $raw = $query->get_posts();

    $posts = [];
    foreach ($raw as $r) {
        $posts[] = new Post(
                $r->ID,
                $r->post_title,
                pLink($r),
                thumb($r),
                pDate($r),
                pExcerpt($r),
                pFirstCategory($r)
            );
    }

    $response = $posts;

    if($json) {
        $response = Request::toJson($posts);
    } 

    if($unstoppable) {
        return $response;
    }

    die($response);
}

function postCount() {
    if(!Request::isGet()) {
        die(Request::error(__("Method not allowed")));
    }    
    

    $query = new WP_Query(buildArgs());
    die(Request::toJson([ 'count' => $query->found_posts ]));
}

function isLoggedIn() {
  return is_user_logged_in();
}

function login($credentials) {
  return wp_signon($credentials);
}

function credentials() {
  return [
    'user_login' => Request::post('username'),
    'user_password' => Request::post('password'),
    'remember' => false
  ];
}

function d($variable) {
  return var_dump($variable);
}