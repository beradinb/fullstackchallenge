<?php

	class URLSearch {

			var $url;									//URL varaible
			var $html;								//HTML content
			var $doc;									//To represent html document
			var $title;								//Page title
			var $link_count;					//Number of links in the page count
			var $unique_link_count;   //Number of unique domains
			var $ga_check;						//Bool for GA check

			//Set url function
	    function set_url ($search_url) {
				$this->url = $search_url;
			}
			//Get url function
			function get_url () {
				return $this->url;
			}
			//Retrieve page content from url
			function set_file_content (){
				//Read page content into a string
				if (@file_get_contents($this->url) == null) {
					$this->html = false;
				} else {
					$this->html = file_get_contents($this->url);
				}
				//$this->html = file_get_contents($this->url);
	    }
			//Return the content
			function get_file_content (){
				return $this->html;
	    }
			//Represents HTML document from html content retrieved
			function set_document (){
				//Page content string into a document
	      $this->doc = new DOMDocument();
	      libxml_use_internal_errors(true);
	      $this->doc->loadHTML($this->html);
	      libxml_use_internal_errors(false);
	      //return $this->doc;
	    }
			//Set title
			function set_title () {
	      $this->title = $this->doc->getElementsByTagName('title')->item('0')->nodeValue;
	    }
			//Return title name of the page
			function get_title () {
	      return $this->title;
	    }
			//finding number of links and unique domains
			function find_link_count () {

					$xpath = new DOMXpath($this->doc);  // Used to locate nodes in a document
					$nodes = $xpath->query('//a');		  // Query for finding <a> tags
				  $counter = 0;											  // Link counter
					$parsed_links = array();					  // An parsed array to store unique domains

					//Foreach to go through all nodes (links)
					foreach($nodes as $node) {
		          //var_dump($node->getAttribute('href'));
		          $node->getAttribute('href');																					// Get the href value
							$current_url = parse_url($node->getAttribute('href'), PHP_URL_HOST);  // Parse URL to get just the host URL
					    $parsed_links[$counter] = $current_url;																// Store parsed URL into an array
		          $counter++;																														// Increment link counter
		      }
					$unique_links = array_unique($parsed_links, SORT_REGULAR); // Remove any duplicate domains
					$this->link_count = $counter;															 // Set the link count value
					$this->unique_link_count = count($unique_links);					 // set the count for unique domains using count array function
			}
			//Return number of links on the page that the user can click on
			function get_link_count () {
				return $this->link_count;
			}
			//Return number of unique domains that these links go to
			function get_unique_link_count () {
				return $this->unique_link_count;
			}
			//Google Analytics check code
			function ga_check () {
				$this->ga_check = false; 						// Setting the ga exists check to false
				$xpath = new DOMXpath($this->doc);  // Used to locate nodes in a document
				$nodes = $xpath->query('//script'); // Query for finding <script> tags

				foreach($nodes as $node) {
						//var_dump($node->getAttribute('src'));
						$current_url = parse_url($node->getAttribute('src'), PHP_URL_HOST);		// Parse URL to get just the host URL

						//Check if GA exists
						if ($current_url === "www.googletagmanager.com") {
							$this->ga_check = true;
						}
				}
			}
			//Google Analytics check code
			function get_ga_check () {
				if ($this->ga_check === true) {
					Return "Yes";
				} else {
					Return "No";
				}
			}

	}

	if (isset($_POST['search_url'])){

		// Instantiating a class, creating an object
		$search_url = new URLSearch();
		$search_url->set_url($_POST['url']);
		$search_url->set_file_content();
		if ($search_url->get_file_content() != false) {
			$search_url->set_document();
			$search_url->set_title();
			$search_url->find_link_count();
		  $search_url->ga_check();
		}
	}
?>

<!DOCTYPE HTML>
<html>
<head>
<title>Fullstack Challenge</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

<!-- CSS -->
<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="css/style.css">

<!-- JS -->
<link rel="stylesheet" href="bootstrap/js/bootstrap.min.js">

</head>

<body class="overflow-hidden">
  <div class="container">
    <div align="center">

    <div class="title">Fullstack Challenge</div>

      <!--Search URL form-->
      <form class="search-form" method="post"  onsubmit="return FormValidation()" align="center">
         <div class="input-group">
           <label class="col-12 search_url_label" for="url" align="center">Enter URL:</label>
           <input class="form-control text_area" id="searchURLTextField" name="url" placeholder="Enter a URL">
           <span class="input-group-btn">
             <button class="btn btn-success search_btn" type="submit" name="search_url">Go</button>
           </span>
         </div>
         <div class="not_found" id="error_message" align="left"></div>
      </form>

			<?php if (isset($search_url) && ($search_url->get_file_content() != false) ) : ?>
			<table class="table">
			  <tbody>
			    <tr class="thead-dark">
			      <th scope="row">Title</th>
			      <td><?php echo $search_url->get_title(); ?></td>
			    </tr>
			    <tr class="thead-dark">
			      <th scope="row">Number of links</th>
			      <td><?php echo $search_url->get_link_count(); ?></td>
			    </tr>
			    <tr class="thead-dark">
			      <th scope="row">Number of unique domains</th>
			      <td><?php echo $search_url->get_unique_link_count(); ?></td>
			    </tr>
					<tr class="thead-dark">
			      <th scope="row">Is Google Analytics present?</th>
			      <td><?php echo $search_url->get_ga_check(); ?></td>
			    </tr>
			  </tbody>
			</table>
			<?php endif ?>
			<?php if (isset($search_url) && ($search_url->get_file_content() == false)) : ?>
        <div class="not_found" align="center">URL not found, try again. Include HTTP or HTTPS</div>
      <?php endif ?>
      </div>

    </div>
  </div>
</body>

<script>
  //Check if text field is empty
  function FormValidation(){
    var urlfield = document.getElementById('searchURLTextField').value;
    if(urlfield == ""){
          //alert('Please Enter a URL');
          document.getElementById('searchURLTextField').style.borderColor = "red"; //Red border around text field
          document.getElementById('error_message').innerHTML= "Please enter a url."; //Error message
					document.getElementById('error_message').style.color = "red";
          return false;
      }
  }
</script>

</html>
