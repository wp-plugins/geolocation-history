<?php
/*  Copyright 2012 Yoran Brondsema (email : yoran.brondsema@gmail.com)
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License, version 2, as 
 *  published by the Free Software Foundation.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *  */
?>
<?php
/*
 * Plugin Name: Geolocation History
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: Keeps a history of geographical locations.
 * Version: 1.0
 * Author: Yoran Brondsema
 * Author URI: http://yoranbrondsema.net
 * License: GPL2
 * */
?>
<?php
define("MYSQL_DATE_FORMAT", "Y-m-d H:i:s");
define("DATETIMEPICKER_DATE_FORMAT", "d/m/Y G:i");

global $lochis_db_version;
global $page;

$lochis_db_version = "1.0";
$page = null;

/* Creates the database table. */
function lochis_install() {
	global $wpdb;
	global $lochis_db_version;

	$table_name = $wpdb->prefix . "geolocation_history";

	$sql = "CREATE TABLE " . $table_name . " (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		label text,
		date timestamp NOT NULL,
		latitude tinytext NOT NULL,
		longitude tinytext NOT NULL,
		UNIQUE KEY id (id)
	);";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);

	update_option("lochis_db", $table_name);
	add_option("lochis_db_version", $lochis_db_version);
}

function lochis_get_form($id, $submit_text, $data) {
	ob_start();
?>
	<form method="post" action="<?php $_SERVER["REQUEST_URI"]; ?>" id="<?php echo $id; ?>" class="location-form">
			<input type="hidden" name="lochis-hidden" value="Y">
			<input type="hidden" name="location-id" value="<?php echo $data['id']; ?>" />
			<div class="row-fluid">
				<div class="span6">
					<label for="<?php echo $id; ?>-label">Label</label>
					<input type="text" id="<?php echo $id; ?>-label" name="location-label" value="<?php echo $data['label'];?>" />
				</div>
			</div>
			<div class="row-fluid">
				<div class="span6">
					<label for="<?php echo $id; ?>-date">Date</label>
					<input type="text" id="<?php echo $id; ?>-date" class="form-date" name="location-date" value="<?php echo $data['date'];?>" />
				</div>
			</div>
			<div class="row-fluid">
				<div class="lat-long span6">
					<div class="row-fluid span12">
						<label for="<?php echo $id; ?>-latitude">Latitude</label>
						<input type="text" id="<?php echo $id; ?>-latitude" name="location-latitude" value="<?php echo $data['latitude']; ?>" />
					</div>
					<label for="<?php echo $id; ?>-longitude">Longitude</label>
					<input type="text" id="<?php echo $id; ?>-longitude" name="location-longitude" value="<?php echo $data['longitude']; ?>" />
					<input type="submit" class="button" value="<?php echo $submit_text; ?>" />
				</div>
				<div class="gmaps-url span6">
					<label for="<?php echo $id; ?>-gmaps-url">Google Maps URL</label>
					<input type="text" id="<?php echo $id; ?>-gmaps-url" name="location-gmaps-url" />
					<input type="button" class="button parse-gmaps-url" value="Parse URL" />
				</div>
			</div>
		</form>
<?php
	$contents = ob_get_contents();
	ob_end_clean();

	return $contents;
}


function lochis_admin() {
	global $wpdb;

	if (!current_user_can('manage_options'))
		wp_die( __('You do not have sufficient permissions to access this page.') );
?>
	<div class="wrap" id="location-history">
<?php
	if ($_POST['lochis-hidden'] == 'Y') {
		/* Insert new location */
		$latitude = $_POST['location-latitude'];
		$longitude = $_POST['location-longitude'];
		$label = $_POST['location-label'];
		$date = DateTime::createFromFormat(DATETIMEPICKER_DATE_FORMAT, $_POST['location-date']);
		$date = $date->format(MYSQL_DATE_FORMAT);
		$res = $wpdb->insert(get_option('lochis_db'),
			array(
				'label' => $label,
				'date' => $date,
				'latitude' => $latitude,
				'longitude' => $longitude
			));
		if (!$res) {
?>
		<div class="alert alert-error">The location '<?php echo $label; ?>' could not be saved to the database.</div>
<?php
		} else {
?>
		<div class="alert alert-success">The location '<?php echo $label; ?>' was successfully added.</div>
<?php
		}
	}

?>
		<div class="metabox-holder">
			<div class="postbox">
				<h3><span>Insert a location</span></h3>
				<div class="inside">
					<?php echo lochis_get_form('insert-location', 'Add location', array()); ?>
				</div>
			</div>
		</div>
		<table class="widefat">
			<thead>
				<tr>
					<th colspan="2">Action</th>
					<th>Label</th>
					<th>Latitude</th>
					<th>Longitude</th>
					<th>Date</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th colspan="2">Action</th>
					<th>Label</th>
					<th>Latitude</th>
					<th>Longitude</th>
					<th>Date</th>
				</tr>
			</tfoot>
			<tbody>
<?php
	$locations = $wpdb->get_results("SELECT * FROM " . get_option("lochis_db") . " ORDER BY date DESC");
	foreach ($locations as $location) {
		$date = DateTime::createFromFormat(MYSQL_DATE_FORMAT, $location->date);
		$location->date = $date->format(DATETIMEPICKER_DATE_FORMAT);
?>
				<tr data-id="<?php echo $location->id; ?>">
					<td><a class="delete action" href="#">Delete</a></td>
					<td><a class="edit action" href="#">Edit</a></td>
					<td class="location-label"><?php echo $location->label; ?></td>
					<td class="location-latitude"><?php echo $location->latitude; ?></td>
					<td class="location-longitude"><?php echo $location->longitude; ?></td>
					<td class="location-date"><?php echo $location->date; ?></td>
				</tr>
				<tr class="edit-row hidden">
					<td colspan="6">
						<?php
							$data = array();
							$data['id'] = $location->id;
							$data['label'] = $location->label;
							$data['latitude'] = $location->latitude;
							$data['longitude'] = $location->longitude;
							$data['date'] = $location->date;
							echo lochis_get_form('edit-location-' . $location->id, 'Save', $data);
						?>
					</td>	
				</tr>
<?php } ?>
			</tbody>
		</table>
	</div>
<?php
}


function lochis_ajax_edit_location() {
	global $wpdb;

	$id = $_POST['id'];
	$label = $_POST['label'];
	$date = DateTime::createFromFormat(DATETIMEPICKER_DATE_FORMAT, $_POST['date']);
	$date = $date->format(MYSQL_DATE_FORMAT);
	$latitude = $_POST['latitude'];
	$longitude = $_POST['longitude'];

	echo $wpdb->update(
		get_option('lochis_db'),
		array(
			'label' => $label,
			'date' => $date,
			'latitude' => $latitude,
			'longitude' => $longitude
		),
		array( 'id' => $id ));

	die();
}

function lochis_ajax_delete_location() {
	global $wpdb;

	$id = $_POST['id'];
	$tab = get_option('lochis_db');
	$query = $wpdb->prepare("DELETE FROM $tab WHERE id=%d", $id);
	echo $wpdb->query($query);

	die();
}

function lochis_admin_actions() {
	global $page;
	$page = add_options_page('Location History Options', 'Location History', 'manage_options', 'location-history', 'lochis_admin');
}

function lochis_load_scripts($hook) {
	global $page;

	if ($hook === $page) {
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-slider');
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_script('datetimepicker', plugins_url('js/jquery-ui-timepicker.js', __FILE__), array('jquery-ui-datepicker', 'jquery-ui-slider'));
		wp_enqueue_script('parseuri', plugins_url('js/parseuri.js', __FILE__));
		wp_enqueue_script('custom', plugins_url('js/script.js', __FILE__), array('jquery', 'datetimepicker'));
	}
?>
<?php
}

function lochis_load_css($hook) {
	global $page;

	if ($hook === $page) {
		wp_enqueue_style('bootstrap', plugins_url('bootstrap/css/bootstrap.min.css', __FILE__));
		wp_enqueue_style('jquery-ui', plugins_url('css/jquery-ui-1.8.23.custom.css', __FILE__));
		wp_enqueue_style('jquery-ui', plugins_url('css/jquery-ui-timepicker.css', __FILE__));
		wp_enqueue_style('custom', plugins_url('css/style.css', __FILE__));
	}
?>
<?php
}

register_activation_hook(__FILE__, 'lochis_install');
add_action('admin_menu', 'lochis_admin_actions');
add_action('admin_enqueue_scripts', 'lochis_load_scripts');
add_action('admin_enqueue_scripts', 'lochis_load_css');
add_action('wp_ajax_delete_location', 'lochis_ajax_delete_location');
add_action('wp_ajax_edit_location', 'lochis_ajax_edit_location');

/* AJAX API */
add_action('wp_ajax_get_location_history', 'lochis_ajax_get_location_history');
add_action('wp_ajax_nopriv_get_location_history', 'lochis_ajax_get_location_history');
add_action('wp_ajax_get_latest_location', 'lochis_ajax_get_latest_location');
add_action('wp_ajax_nopriv_get_latest_location', 'lochis_ajax_get_latest_location');
?>
<?php
/*********************************************/
/**************** API ************************/
/*********************************************/

/*
 * Returns an array of the whole history of locations, sorted by date from oldest to most recent.
 * Each array element $el is itself an associate array containing the following elements:
 * $el['date']: the date associated with the location
 * $el['label']: the label associated with the location
 * $el['latitude']: the latitude of the location
 * $el['longitude']: the longitude of the location
 */
function lochis_get_location_history() {
	global $wpdb;
	$query = 'SELECT date, label, latitude, longitude FROM ' . get_option('lochis_db') . ' ORDER BY date ASC';
	return $wpdb->get_results($query, ARRAY_A);
}

/*
 * Returns a JSON-encoded array of the whole history of locations, sorted by date from oldest to most recent.
 * Each array element 'el' is a JSON-object containing the following elements:
 * el.date: the date associated with the location
 * el.label: the label associated with the location
 * el.latitude: the latitude of the location
 * el.longitude: the longitude of the location
 */
function lochis_ajax_get_location_history() {
	echo json_encode(lochis_get_location_history());
	die();
}

/*
 * Returns the most recent location $el, an associative array containing the following elements:
 * $el['date']: the date associated with the location
 * $el['label']: the label associated with the location
 * $el['latitude']: the latitude of the location
 * $el['longitude']: the longitude of the location
 */
function lochis_get_latest_location() {
	global $wpdb;
	$table = get_option('lochis_db');
	$query = 'SELECT date, label, latitude, longitude FROM ' . $table . ' WHERE date=(SELECT MAX(date) FROM ' . $table . ')';
	return $wpdb->get_row($query, ARRAY_A);
}

/*
 * Returns a JSON-object of the most recent location containing:
 * el.date: the date associated with the location
 * el.label: the label associated with the location
 * el.latitude: the latitude of the location
 * el.longitude: the longitude of the location
 */
function lochis_ajax_get_latest_location() {
	echo json_encode(lochis_get_latest_location());
	die();
}
?>
