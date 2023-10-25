<?php


require_once __DIR__ . '/../maincore.php';
require_once __DIR__ . '/../models/users.php';
require_once THEMES . 'templates/admin_header.php';
pageAccess('ACC');
#region jstree
// Add jstree to head
add_to_head('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css" />');
add_to_head('<script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/jstree.min.js"></script>');
#endregion jstree
#region datatree
add_to_head('<script src="/includes/datatree/data-tree.js"></script>');
add_to_head('<link rel="stylesheet" href="/includes/datatree/data-tree.css" />');
#endregion datatree
?>

<style>
  .add-margin {
    margin: 10px 3px
  }

  .mark {
    text-justify: justify;
    background-color: rgba(255, 255, 0, 0.1);
  }
</style>

<div class="row" style="margin-left: 0; padding: 10px">
  <div class="col-md-6">
    <div class="row">
      <div class="col-md-12 add-margin">
        <label for="user">User:</label>
        <select class="form-control" id="user" onchange="changeUser()">
          <option value="0">none</option>
          <?php
          $users = getAllUsers();
          function cmp($a, $b)
          {
            return strcmp(strtolower($a["user_name"]), strtolower($b["user_name"]));
          }

          usort($users, "cmp");
          foreach ($users as $user) {
            $name = $user['user_name'];
            $id = $user['user_id'];
            echo "<option value=\"$id\">$name</option>";
          }
          ?>
        </select>
      </div>
      <div class="col-md-12 add-margin">
        <label for="user">Expire Date:</label>
        <input type="date" class="form-control" id="expire-time">
      </div>
      <div class="col-md-12 add-margin">
        <label for="user">Expire Date (Days):</label>
        <input type="number" class="form-control" id="expire-time-day" value="0">
      </div>
      <div class="col-md-12 add-margin">
        <p>User Expire Date: <span id="current_access"></span></p>
      </div>
      <div class="col-md-12">
        <p class="mark">
          If you don't define the time, then the time won't change, and only the access will change
        </p>
      </div>
      <div class="col-md-12">
        <p class="mark">
          Only one option can be used by you. Either define an expiry date on the calendar or define the remaining days
        </p>
      </div>
      <div class="col-md-12 add-margin">
        <button id="submit" class="btn btn-success">Grant Access</button>
      </div>

    </div>
  </div>
  <div class="col-md-6">
    <div class="row">
      <div class="col-md-12 add-margin">
        <div>
          <label for="search">Search:</label>
          <input type="text" class="form-control" id="search">
        </div>
        <div>
          <label style="display: inline-block;" for="select_all">Select All:</label>
          <br>
          <input style="display: inline-block;" type="checkbox" id="select_all">
          <br>
          <br>
        </div>
        <div id="frmt"></div>
      </div>
    </div>
  </div>
</div>



<script>
  // Define the number of milliseconds in a day
  const millisecondInDay = 86400000;

  // Declare variables
  let selectedRoutesId = [];
  let routes = [];
  let access = [];
  // Run the bootstrap function when the document is ready
  $(() => {
    bootstrap();
  });

  // Bootstrap function
  function bootstrap() {
    // Call the getRoutes function to fetch routes data
    getRoutes();

    // Event handler for the "expire-time-day" change event
    $("#expire-time-day").on("change", (e) => {
      // Disable the "expire-time" input if the selected value is greater than 0
      if (Number(e.target.value) > 0)
        $("#expire-time").attr("disabled", "disabled");
      else
        $("#expire-time").removeAttr("disabled");
    });

    // Event handler for the "submit" button click event
    $("#submit").on("click", () => {
      // Get the selected user value
      const selectedUser = $("#user option:selected").val();

      // Calculate the expiration time based on the selected date and add one day
      let expireTime = (new Date($("#expire-time").val()).getTime() + millisecondInDay) / 1000;

      // Get the number of days to expire from the "expire-time-day" input
      const dayOfExpire = Number($("#expire-time-day").val()) + 1;

      // If the dayOfExpire is not 0, calculate the expiration time based on the number of days
      if (dayOfExpire !== 1) {
        let now = new Date();
        now.setSeconds(0);
        now.setMinutes(0);
        now.setHours(0);
        now = now.getTime() + (dayOfExpire * millisecondInDay);
        expireTime = new Date(now).getTime() / 1000;

        // Print debug information about the calculated expiration time
        console.log({
          expireTime,
          dayOfExpire,
          now: new Date(expireTime).toUTCString()
        });
      }

      // Disable the "submit" button
      $("#submit").attr("disabled", "disabled");

      // Create a new FormData object
      const formData = new FormData();
      formData.append("user", selectedUser);

      // Append the expiration time to the formData if it's a valid number
      if (!Number.isNaN(expireTime))
        formData.append("expire_time", expireTime);

      // Append the selected routes to the formData
      formData.append("routes", selectedRoutesId.join(","));

      // Append the action parameter to the formData
      formData.append("action", "edit");

      // Send a POST request to the API endpoint with the formData
      fetch("/api/v1/route_access.php", {
          body: formData,
          method: "POST"
        })
        .then(async res => await res.json())
        .then((response) => {
          // Call the getAccess function to update the access data
          getAccess();

          // Print the response data
          console.log(response);

          // Show success message if the response code is 200, otherwise show failure message
          if (response.code === 200) {
            alert("Success");
            window.location.reload();
          } else {
            alert("Failed");
          }
        })
        .catch((e) => alert(`Error: ${e}`))
        .finally(() => $("#submit").removeAttr("disabled"));
    });

    // Event handler for the "select_all" checkbox change event
    $("#select_all").on("change", () => {
      // Get the status of the "select_all" checkbox
      const status = $("#select_all").is(':checked');

      // Create the tree with the parsed menu data, access data, and the checkbox status
      createTree(parseMenuData(routes), access.map(i => i.id), status);
    });
  }

  // Function to fetch the routes data
  function getRoutes() {
    // Send a GET request to the API endpoint to fetch menu tree data
    fetch("/api/v1/get_menu_tree.php?key=text&type=json")
      .then((res) => res.json())
      .then((res) => {
        // Store the fetched routes data
        routes = res.sort((a, b) => Number(a.unique_id) - Number(b.unique_id));;

        // Create the tree with the parsed menu data and access data
        createTree(parseMenuData(routes), access.map(i => i.id));

        // Call the getAccess function to fetch access data
        getAccess();
      })
      .catch((e) => {
        console.log(e);
        alert("Internal Server Error");
      });
  }

  // Function to fetch the access data
  function getAccess() {
    // Send a GET request to the API endpoint to fetch all access data
    fetch("/api/v1/route_access.php?action=get_all")
      .then(async (res) => {
        // Parse the response as JSON
        const rawAccess = JSON.parse(await res.text());
        access = rawAccess.map(i => {
          i.access = i.access.split(",").filter(j => {
            const child = routes.find(z => z.parent == j);
            return child === undefined;
          }).join(",")
          return i;
        })
        console.log("")
      })
      .catch((e) => {
        console.log(e);
        alert("Internal Server Error");
      });
  }

  // Function to handle user change event
  function changeUser(item) {
    // Get the selected user value
    const value = document.getElementById('user').value;
    // Get the current_access element
    const current_access = $('#current_access');

    // Find the access object for the selected user
    const accessObject = access.find(item => String(item.user) === String(value));
    if (accessObject === undefined) return;

    // Get the user's access and expiration time
    const userAccess = accessObject ? accessObject.access.split(",") : [];
    const expireTime = new Date(accessObject.expire_time * 1000);

    // Check if the expiration time has passed
    const isExpired = expireTime < Date.now();

    // Calculate the remaining time in days
    const remainingTime = ~~((expireTime - Date.now()) / millisecondInDay);

    // Check if all routes are selected for the user
    if (routes.filter(i => i.period !== "").length <= userAccess.length)
      $("#select_all").prop("checked", "checked");
    else
      $("#select_all").removeAttr("checked");

    // Set the color and text of the current_access element based on expiration status
    current_access.css("color", isExpired ? "red" : "green");
    current_access.text(`${expireTime.toDateString()} ${isExpired ? "(Expired)" : `(${remainingTime} ${remainingTime > 1 ? 'Days' : `Day`})`}`);

    // Create the tree with the parsed menu data and the user's access
    createTree(parseMenuData(routes), userAccess);
  }

  // Function to parse menu data into the required format
  function parseMenuData(data, access = []) {
    return data.map((item) => {
      return {
        id: item.id.toString(),
        text: `${item.unique_id.toString()}-${item.name.toString()}`,
        parent: item.parent.toString() === "0" ? "#" : item.parent.toString(),
      };
    });
  }

  // Function to create the tree using the jstree library
  function createTree(data, userAccess, select_all = undefined) {
    const config = {
      "plugins": ["wholerow", "checkbox", "search"],
      'core': {
        'data': data
      }
    };

    // Initialize the jstree with the provided configuration
    $('#frmt').on('changed.jstree', function(e, data) {
      selectedRoutesId = data.selected;
    }).jstree(config);

    try {
      // Deselect all nodes in the tree
      deselect_all_node()

      // Select the nodes corresponding to the user's access
      $('#frmt').jstree("select_node", userAccess);
    } catch (error) {
      console.error(error);
    }

    // Handle the select_all parameter if provided
    if (select_all !== undefined) {
      deselect_all_node()
      if (select_all === true) {
        // Select all nodes in the tree
        // $('#frmt').jstree().select_all(false);
        select_all_node()
      }
    }

    // Event handler for the "search" input change event
    $("#search").on("change", (e) => {
      // Perform a search on the jstree with the input value
      $("#frmt").jstree(true).search(e.target.value);
    });
  }

  function deselect_all_node() {
    $('.jstree-anchor').each(function() {
      if ($(this).hasClass('jstree-clicked')) {
        $(this).click();
      }
    });
  }

  function select_all_node() {
    $('.jstree-anchor').each(function() {
      if (!$(this).hasClass('jstree-clicked')) {
        $(this).click();
      }
    });
  }
</script>

<?php require_once THEMES . 'templates/footer.php'; ?>