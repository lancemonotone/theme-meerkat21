<?php 

// get all the people from this department that have posts
$unixs = $query->posts;
$unixs = array_map(
	function( $post ) {
		return $post->profile_unix;
	},
	$unixs
);

$dept = new WilliamsPeopleDirectoryPlugin();
$people = $dept->get_people($unixs);

?>


<div class="ask-an-eph-grid quad-container">

<?php while ( have_posts() ): the_post(); ?>

<?php 

  $context = \Timber\Timber::get_context();
  $post = new Timber\Post();

  $ldap = [];
  foreach ($people as $key => $val) {
    if ($val['userid'] === $post->profile_unix) {
      $ldap = $val;
    }
  }

  $context = array_merge(
      $context,
      array(
          'post'          => $post,
          'ldap'          => $ldap,
          'profile_dept'  => $profile_dept, // from FacetWP template
          'rando'         => get_stylesheet_directory_uri() . '/assets/img/placeholder.jpg',
          'edit_link'     => new Timber\FunctionWrapper('edit_post_link', array(__('Edit'), '<span class="edit-me">', '</span>')),
      )
  );


Timber::render('/views/profile.twig', $context);?>

<?php endwhile; ?>
</div>
