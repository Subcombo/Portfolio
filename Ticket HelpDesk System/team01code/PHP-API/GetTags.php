<?php
  include '../login-toolkit/login_setup.php';
  include '../PHP-DB/api.php';
  $tags = \db\search_problem_tags('');
  $results = array();
  foreach ($tags as $tag)
  {
    $details = \db\get_problem_tag($tag);
    $parent_path = [];
    if (isset($details["parent_tag_id"]))
      $parent_path = \db\meta_get_problem_tag_path_array($details["parent_tag_id"]);

    $results[] = [
      "tag_id" => $tag,
      "tag_name" => $details['name'],
      "parent_tag_id" => $details["parent_tag_id"],
      "parent_path" => $parent_path
    ];
  }
  echo json_encode($results);
?>
