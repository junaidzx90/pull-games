<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.fiverr.com/junaidzx90
 * @since      1.0.0
 *
 * @package    Pull_Games
 * @subpackage Pull_Games/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Pull_Games
 * @subpackage Pull_Games/admin
 * @author     Developer Junayed <admin@easeare.com>
 */
class Pull_Games_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/pull-games-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/pull-games-admin.js', array( 'jquery' ), $this->version, false );
		wp_localize_script($this->plugin_name,"pullgames",array(
			"ajaxurl" => admin_url("admin-ajax.php")
		) );

	}

	function admin_menu_page(){
		add_menu_page("Pull Games","Pull Games","manage_options", "pull-games",[$this, "pullgames_menu_html"], "dashicons-cloud-saved",45 );
	}

	function get_image_url($placeid){
		$url = "https://thumbnails.roblox.com/v1/places/gameicons?placeIds=$placeid&size=512x512&format=Png&isCircular=false";

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		$headers = array(
		"Accept: application/json",
		);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		//for debug only!
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

		$resp = curl_exec($curl);
		curl_close($curl);

		$resp = json_decode($resp, true);
		if(!empty($resp['data'])){
			return $resp['data'][0]['imageUrl'];
		}
	}

	function get_search_results($types, $query, $maxrows = null){
		switch ($types) {
			case 'id':
				$url = "https://games.roblox.com/v1/games?universeIds=$query";

				$curl = curl_init($url);
				curl_setopt($curl, CURLOPT_URL, $url);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

				//for debug only!
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

				$resp = curl_exec($curl);
				curl_close($curl);

				$resp = json_decode($resp, true);
				
				if(!empty($resp['data'])){
					$data = $resp['data'][0];
					
					$imageUrl = $this->get_image_url($data['rootPlaceId']);
					
					$storedata = array(
						"name" => $data['name'],
						"description" => $data['description'],
						"rootPlaceId" => $data['rootPlaceId'],
						"id" => $data['id'],
						"price" => $data['price'],
						"maxPlayers" => $data['maxPlayers'],
						"image_token" => $imageUrl,
						"created" => $data['created'],
						"updated" => $data['updated'],
						"createVipServersAllowed" => $data['createVipServersAllowed'],
						"creator" => $data['creator'],
						"genre" => $data['genre']
					);
					return $storedata;
				}
				break;
			
			case 'keyword':
				$url = "https://games.roblox.com/v1/games/list?model.keyword=$query&model.maxRows=$maxrows";

				$curl = curl_init($url);
				curl_setopt($curl, CURLOPT_URL, $url);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

				//for debug only!
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

				$resp = curl_exec($curl);
				curl_close($curl);

				$resp = json_decode($resp, true);
				
				if(!empty($resp['games'])){
					$games = $resp['games'];

					$storedata = [];
					if(is_array($games) && sizeof($games)> 0){
						foreach($games as $game){
							set_time_limit(0);
							$imageUrl = $this->get_image_url($game['placeId']);
							$storedata[] = array(
								"name" => $game['name'],
								"description" => $game['gameDescription'],
								"rootPlaceId" => $game['placeId'],
								"id" => $game['universeId'],
								"price" => $game['price'],
								"image_token" => $imageUrl,
								"creator" => $game['creatorName'],
								"genre" => $game['genre']
							);
						}
					}
					
					return $storedata;
				}
				break;
		}
		
	}

	function pullgames_menu_html(){
		?>
		<h3>Pull Games</h3>
		<hr>
		<div class="pullrow">
			<div class="pullheading">
				<label for="search_query">Search By universeId's</label>
			</div>
			<div class="pullfield">
				<form action="" method="post">
					<textarea placeholder="3703297009, 3359479079, 3558292888	" name="search_query" id="search_query"><?php
						if(isset($_POST['search_query'])){
							echo $_POST['search_query'];
						}
						?></textarea>
					<input type="submit" value="Search" name="search_query_action" class="button-primary">
				</form>
			</div>
		</div>
		<div class="pullrow">
			<div class="pullheading">
				<label for="keyword_search">Search By Keyword</label>
			</div>
			<div class="pullfield">
				<form action="" method="post">
					<input type="text" placeholder="Keyword" value="<?php echo ((isset($_POST['keyword_search']))?$_POST['keyword_search']:'') ?>" name="keyword_search">
					<input type="number" value="<?php echo ((isset($_POST['maxRows']))?$_POST['maxRows']:'') ?>" name="maxRows" placeholder="Maximum rows">
					<input type="submit" value="Search" name="keyword_search_action" class="button-primary">
				</form>
			</div>
		</div>

		<?php
		if(isset($_POST['search_query_action'])){
			?>
			<div class="pullrow">
				<div class="pullheading">
					<label>Results</label>
				</div>
				<div class="respose">
					<table id="result_table">
						<thead>
							<tr>
								<th></th>
								<th>Name</th>
								<th>Description</th>
								<th>PlaceID</th>
								<th>ID</th>
								<th>Price</th>
								<th>maxPlayers</th>
								<th>Created</th>
								<th>Updated</th>
								<th>createVipServersAllowed</th>
								<th>Creator</th>
							</tr>
						</thead>

						<tbody>
							<?php
							$search_query = ((isset($_POST['search_query']))? $_POST['search_query']: '');
							$search_query = sanitize_text_field($search_query );
							$search_query = explode(",", $search_query);

							$is_error = false;
							$storeData = [];
							if(is_array($search_query) && sizeof($search_query) > 0){
								$keys = 1;
								foreach($search_query as $query){
									$universeId = intval($query);
									$response = $this->get_search_results("id",$universeId);
									$storeData[] = $response;
									
									if($response !== null){
										?>
										<tr>
											<td><?php echo $keys; ?></td>
											<td><?php echo $response['name']; ?></td>
											<td><?php echo substr($response['description'], 0, 50); ?></td>
											<td><?php echo $response['rootPlaceId']; ?></td>
											<td><?php echo $response['id']; ?></td>
											<td><?php echo $response['price']; ?></td>
											<td><?php echo $response['maxPlayers']; ?></td>
											<td><?php echo date("F j, Y, g:i a", strtotime($response['created'])); ?></td>
											<td><?php echo date("F j, Y, g:i a", strtotime($response['updated'])); ?></td>
											<td><?php echo (($response['createVipServersAllowed'])? "True": "False"); ?></td>
											<td><?php echo $response['creator']['name']; ?></td>
										</tr>	
										<?php
									}else{
										$is_error = true;
										echo '<tr><td>No universeId detected.</td></tr>';
									}
									$keys++;
								}
								
								update_option( "pullgames_instant_data", $storeData ); // Store ontime data to import
							}
							?>
						</tbody>
					</table>

					<?php
					if(!$is_error){
						?>
						<div class="import_button">
							<button id="import_pullgames" class="button-secondary">Import into post</button>
							<svg version="1.1" id="loader-1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="50px" height="50px" viewBox="0 0 40 40" enable-background="new 0 0 40 40" xml:space="preserve">
								<path opacity="0.2" fill="#000" d="M20.201,5.169c-8.254,0-14.946,6.692-14.946,14.946c0,8.255,6.692,14.946,14.946,14.946
								s14.946-6.691,14.946-14.946C35.146,11.861,28.455,5.169,20.201,5.169z M20.201,31.749c-6.425,0-11.634-5.208-11.634-11.634
								c0-6.425,5.209-11.634,11.634-11.634c6.425,0,11.633,5.209,11.633,11.634C31.834,26.541,26.626,31.749,20.201,31.749z"></path>
								<path fill="#135e96" d="M26.013,10.047l1.654-2.866c-2.198-1.272-4.743-2.012-7.466-2.012h0v3.312h0
								C22.32,8.481,24.301,9.057,26.013,10.047z">
								<animateTransform attributeType="xml" attributeName="transform" type="rotate" from="0 20 20" to="360 20 20" dur="0.9s" repeatCount="indefinite"></animateTransform>
								</path>
							</svg>
						</div>
						<?php
					}
					?>
					
				</div>
			</div>
			<?php
		}
		if(isset($_POST['keyword_search_action'])){
			?>
			<div class="pullrow">
				<div class="pullheading">
					<label>Results</label>
				</div>
				<div class="respose">
					<table id="result_table">
						<thead>
							<tr>
								<th></th>
								<th>Name</th>
								<th>Description</th>
								<th>PlaceID</th>
								<th>Price</th>
								<th>Creator</th>
							</tr>
						</thead>

						<tbody>
							<?php
							$search_query = ((isset($_POST['keyword_search']))? $_POST['keyword_search']: '');
							$search_query = sanitize_text_field($search_query );

							$maxrows = 5;
							if(isset($_POST['maxRows'])){
								$maxrows = intval($_POST['maxRows']);
							}

							$response = $this->get_search_results("keyword", $search_query, $maxrows);
							if(is_array($response) && sizeof($response)>0){
								$keys = 1;
								foreach($response as $resp){
									?>
									<tr>
										<td><?php echo $keys; ?></td>
										<td><?php echo $resp['name']; ?></td>
										<td><?php echo substr($resp['description'], 0, 50); ?></td>
										<td><?php echo $resp['rootPlaceId']; ?></td>
										<td><?php echo $resp['price']; ?></td>
										<td><?php echo $resp['creator']; ?></td>
									</tr>	
									<?php
									$keys++;
								}
							}
							update_option( "pullgames_instant_data", $response ); // Store ontime data to import
							?>
						</tbody>
					</table>

					<div class="import_button">
						<button id="import_pullgames" class="button-secondary">Import into post</button>
						<svg version="1.1" id="loader-1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="50px" height="50px" viewBox="0 0 40 40" enable-background="new 0 0 40 40" xml:space="preserve">
							<path opacity="0.2" fill="#000" d="M20.201,5.169c-8.254,0-14.946,6.692-14.946,14.946c0,8.255,6.692,14.946,14.946,14.946
							s14.946-6.691,14.946-14.946C35.146,11.861,28.455,5.169,20.201,5.169z M20.201,31.749c-6.425,0-11.634-5.208-11.634-11.634
							c0-6.425,5.209-11.634,11.634-11.634c6.425,0,11.633,5.209,11.633,11.634C31.834,26.541,26.626,31.749,20.201,31.749z"></path>
							<path fill="#135e96" d="M26.013,10.047l1.654-2.866c-2.198-1.272-4.743-2.012-7.466-2.012h0v3.312h0
							C22.32,8.481,24.301,9.057,26.013,10.047z">
							<animateTransform attributeType="xml" attributeName="transform" type="rotate" from="0 20 20" to="360 20 20" dur="0.9s" repeatCount="indefinite"></animateTransform>
							</path>
						</svg>
					</div>
					
				</div>
			</div>
			<?php
		}
	}

	function setfeatured_image( $image_url, $post_id  ){
		$image_name       = 'wp-header-logo.png';
		$upload_dir       = wp_upload_dir();
		$image_data       = file_get_contents($image_url);
		$unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name );
		$filename         = basename( $unique_file_name );

		if( wp_mkdir_p( $upload_dir['path'] ) ) {
			$file = $upload_dir['path'] . '/' . $filename;
		} else {
			$file = $upload_dir['basedir'] . '/' . $filename;
		}

		file_put_contents( $file, $image_data );
		$wp_filetype = wp_check_filetype( $filename, null );

		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title'     => sanitize_file_name( $filename ),
			'post_content'   => '',
			'post_status'    => 'inherit'
		);

		$attach_id = wp_insert_attachment( $attachment, $file, $post_id );
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
		wp_update_attachment_metadata( $attach_id, $attach_data );
		set_post_thumbnail( $post_id, $attach_id );
	}

	function pullgames_imports(){
		$games = get_option("pullgames_instant_data" );
		if(is_array($games) && sizeof($games) > 0){
			foreach($games as $game){
				set_time_limit(0);

				$args1 = array(
					'post_type' => 'roblox-experiences',
					'post_title' => $game['name'],
					'post_status' => 'publish',
					'post_author' => 1,
					'post_content' => $game['description'],
					'post_category' => array(),
					'meta_input' => array(
						'place_id' => ((array_key_exists("rootPlaceId", $game))?$game['rootPlaceId']:''),
						'universe_id' => $game['id'],
						'image_token' => $game["image_token"],
						'price' => $game['price'],
						'max_players' => ((array_key_exists("maxPlayers", $game))?$game['maxPlayers']:''),
						'roblox_created' => ((array_key_exists("created", $game))?$game['created']:''),
						'roblox_updated' => ((array_key_exists("updated", $game))?$game['updated']:''),
						'create_vip_servers_allowed' => ((array_key_exists("createVipServersAllowed", $game))?$game['createVipServersAllowed']:'')
					)
				);

				$args2 = array(
					'post_type' => 'roblox-creators',
					'post_title' => ((is_array($game['creator']) && array_key_exists("name", $game['creator']))?$game['creator']['name']:$game['creator']),
					'post_status' => 'publish',
					'post_author' => 1,
					'post_content' => '',
					'meta_input' => array(
						'roblox_id' => ((is_array($game['creator']) && array_key_exists("id", $game['creator']))?$game['creator']['id']:''),
						'creator_type' => ((is_array($game['creator']) && array_key_exists("type", $game['creator']))?$game['creator']['type']:''),
						'verified_creator' => ((is_array($game['creator']) && array_key_exists("hasVerifiedBadge", $game['creator']))?(($game['creator']['hasVerifiedBadge'])? "True": "False"):''),
					)
				);

				$pid1 = wp_insert_post($args1);
				$pid2 = wp_insert_post($args2);
				
				$this->setfeatured_image($game["image_token"], $pid1);
				$this->setfeatured_image($game["image_token"], $pid2);
				
				wp_set_object_terms( $pid1, $game['genre'], "genre", false );

			}
		}
		
		echo json_encode(array("success" => "Success"));
		die;
	}
}
