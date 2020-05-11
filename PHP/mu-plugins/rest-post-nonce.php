<?php
// POST PARAMETER
add_action('rest_api_init', function () {
    register_rest_route( 'owt/v1', 'rest03',array(
                  'methods'  => WP_REST_Server::CREATABLE,
                  'callback' => 'rest03_route',
                  'args'     => array (
                        'title'  => array( 
                            'type'             => 'string',
                            'required'         => true,
                            'validate_callback' => function($param){
                                if (strlen($param) > 4) {
                                    return true;
                                } else {
                                    return false;
                                }
                            }
                        ),
                        'content'  => array(
                            'type'     => 'string',
                            'required' => true,
                            'validate_callback' => function($param){
                                if (strlen($param) > 4 ) {
                                    return true;
                                } else {
                                    return false;
                                }
                            }
                        ),
                        'jwt'    => array(
                            'type' => 'integer',
                            'required' => true,
                            'validate_callback' => function($param){
                                if ($param > -1) {
                                    return true;
                                } else {
                                    return false;
                                }
                            }
                        ),
                        // this is the nonce from X-WP-NONCE function
                        'nonce'    => array(
                            'type' => 'string',
                            'required' => true,
                            'validate_callback' => function($param){
                                if ($param > -1) {
                                    return true;
                                } else {
                                    return false;
                                }
                            }
                        )
                  )
        ));
  });
  function rest03_route(WP_REST_Request $request) { // works without WP_REST_Request
        $request_type = $_SERVER['REQUEST_METHOD'];
        
        if ($request_type == "POST") { 
            $parameters = array(
                "title"   => $request->get_param("title"),
                "content" => $request->get_param("content"),
                "jwt"     => $request->get_param("jwt"),
                "nonce"   => $request->get_param("nonce")
                );  
            // Do standard validations
            $title = sanitize_text_field($request->get_param("title"));
            $content = sanitize_text_field($request->get_param("content"));
            $jwt = $request->get_param("jwt");
            $nonce = strval($request->get_param("nonce"));
            // 'NoncePageTest' was name or key we gave the nonce on the form page
            $check = wp_verify_nonce( $nonce, 'NoncePageTest' );
            switch ( $check ) {
                case 0:
                    $message = 'Nonce FAILS. ';
                     break;
                case 1:
                    $message = 'VALID - Nonce is less than 12 hours old. ';
                    break;
                case 2:
                    $message = 'VALID - Nonce is between 12 and 24 hours old. ';
                    break;
                default:
                    $message = 'Nonce is invalid. ';
            }
            if ($check == 1 || $check ==2) {
                // Create post object
                $my_post = array(
                    'post_title'    => $title,
                    'post_content'  => $content,
                    'post_status'   => 'publish',
                    'post_author'   => 1,
                    'post_category' => array(3 )
                );
                
                // Insert the post into the database
                wp_insert_post( $my_post );
                // send response message as needed...
                return array( 
                    "status"         => "SUCCESS<br>", 
                    "method"         => "POST<br>", 
                    "message"        => $message."<br>", 
                    "jwt received"   => $jwt."<br>",
                    "nonce received" => $nonce."<br>",
                    "parameters"     => $parameters
                );
            }
            else {
                return array( 
                    "status"         => "FAILURE<br>", 
                    "message"        => $message."<br>"     
                );
            }
           
        }
  }
?>
