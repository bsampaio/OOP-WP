<?php

namespace OOP_WP;

require  __DIR__ . '/../functions.php';

class Post {
    public $id;
    public $title;
    public $permalink;
    public $thumbnail;
    public $created_at;
    public $category;
    public $excerpt;
    public $raw;
    public $slug;

    public function __construct($id, 
                                $title, 
                                $permalink, 
                                $thumbnail, 
                                $created_at, 
                                $excerpt,
                                $category = 1,
                                $slug = null,
                                $raw = null) {

        $this->id = $id;
        $this->title = $title;
        $this->permalink = $permalink;
        $this->thumbnail = $thumbnail;
        $this->created_at = $created_at;
        $this->category = $category;
        $this->excerpt = $excerpt;
        $this->raw = $raw;
        $this->slug = $slug;
    }

    public static function convert(array $posts) {
      $response = [];
      foreach ($posts as $key => $post) {
        $response[] = new Post(
            $post->ID,
            $post->post_title,
            pLink($post),
            thumb($post),
            pDate($post),
            pExcerpt($post),
            pFirstCategory($post),
            $post->post_name,
            $post
        );
      }
      return $response;
    }

    public static function filter(array $posts, $fn) {
      return array_filter($posts, $fn);
    }

    public static function find($id) {
        $post = get_post($id);
        if(!$post) {
          return null;
        }
        $post = new Post(
            $post->ID,
            $post->post_title,
            pLink($post),
            thumb($post),
            pDate($post),
            pExcerpt($post),
            pFirstCategory($post),
            $post->post_name,
            $post
        );

        return $post;
    }

    public function getContent() {
      if(!$this->raw) {
        return '';
      }

      return $this->raw->post_content;
    }

    public function getField($key) {
      return get_field($key, $this->id);
    }

    public function updateField($key, $value) {
      return update_field($key, $value, $this->id);
    }

    public function getCategories() {
      $idList = wp_get_post_categories($this->id);
      $categories = [];
      foreach ($idList as $id) {
        $category = get_category($id);
        $categories[] = [
          'id' => $id,
          'link' => get_category_link( $category->term_id ),
          'name' => $category->name,
          'raw' => $category
        ];
      }

      return $categories;
    }

    public function getDate($format = 'd/m/Y') {
      return pDate($this->raw, $format);
    }

    public function getDateTime($format = 'd/m/Y G:i') {
      return $this->getDate($format);
    }

    public function addMeta($key, $value, $unique = false) {
      return add_post_meta($this->id, $key, $value, $unique);
    }

    public function getMeta($key, $single = false) {
      return get_post_meta($this->id, $key, $single);
    }

    public function next() {
      global $post;

      $oldGlobal = $post;
      $post = get_post($this->id);
      $next = get_next_post();
      $post = $oldGlobal;

      return self::find($next->ID);
    }

    private static function buildArgs($type = "post", $ppp = -1, array $merge = []) {
        
        $page = ($page === '') ? 1 : $page;
        $args = [
          'post_type' => $type,
          'post_status' => 'publish',
          'posts_per_page' => $ppp
        ];

        $args = array_merge($args, $merge);

        return $args;
    }

    private static function doQuery($type = "post", $ppp, array $merge = []) {
      $query = new WP_Query(self::buildArgs($type, $ppp, $merge));
      return self::convert($query->get_posts());
    }

    public static function first($type = 'post') {
      $posts = self::doQuery($type, 1, []);
      if(!empty($posts)) {
        return $posts[0];
      }
      return [];
    }

    public static function setupFunctions() {
      require_once __DIR__ . '/../functions.php';
    }
}