<?php
require_once __DIR__ . '/../maincore.php';
require_once __DIR__ . '/../models/users.php';
require_once THEMES . 'templates/admin_header.php';
pageAccess("E");
#region jstree
// Add jstree to head
add_to_head('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css" />');
add_to_head('<script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/jstree.min.js"></script>');
#endregion jstree
?>


<style>
  .container {
    width: 100%;
    padding: 20px;
  }

  li {
    cursor: pointer;
    user-select: none;
  }

  sup {
    color: red;
  }

  input,
  select {
    margin: 5px;
  }
</style>


<div class="container">
  <div class="row">
    <div class="col-md-6" class="parent-selector-wrapper">
      <div class="form-group">
        <label>Search a Route:</label>
        <input type="text" class="form-control" style="width: 50%;" placeholder="Search route" id="search-route" />
      </div>
      <p>Select a route:</p>
      <div id="parent"></div>
    </div>
    <div class="col-md-6" class="parent-selector-wrapper">
      <div class="form-group">
        <label>Search a Route Parent:</label>
        <input type="text" class="form-control" style="width: 50%;" placeholder="Search route parent" id="search-parent" />
      </div>
      <p>Select route parent:</p>
      <div id="parent_id"></div>
      <br>
      <div class="form-check">
        <input class="form-check-input" id="parent_checkbox" type="checkbox" onchange="rootChange()">
        <label class="form-check-label" for="defaultCheck1">
          Set as root
        </label>
      </div>
    </div>
    <hr />

    <div class="col-md-4">
      <div class="form-group">
        <label>Route Title<sup>*</sup>:</label>
        <input type="text" class="form-control" placeholder="Route Title" id="name" />
      </div>
      <div class="form-group">
        <label>Unique Id<sup>*</sup>:</label>
        <input type="text" class="form-control" placeholder="Unique Id" id="unique_id" />
      </div>
      <div class="form-group">
        <label>Unit:</label>
        <input type="text" class="form-control" placeholder="Unit" id="unit" />
      </div>
    </div>
    <div class="col-md-4">
      <div class="form-group">
        <label>Time period:</label>
        <input type="text" class="form-control" placeholder="Route Period" id="period" />
      </div>
      <div>
        <ul id="auto_complete">

        </ul>
      </div>
      <div class="form-group">
        <label>Status:</label>
        <select class="form-select form-control" id="is_active" aria-label="Is active">
          <option value="1" selected>active</option>
          <option value="0">deactive</option>
        </select>
      </div>
    </div>
    <div class="col-md-12 container jumbotron">
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label>Alert Content:</label>
            <input class="form-control" id="alert-content" placeholder="Write a message..." type="text">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label>Alert Type:</label>
            <select class="form-select form-control" id="alert-color" aria-label="Is active">
              <option value="info" selected>Info (Blue)</option>
              <option value="warning">Warning (Yellow)</option>
              <option value="success">Success (Green)</option>
              <option value="danger">Danger (Red)</option>
            </select>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <Button class="btn btn-primary" id="add-alert">Add Alert</Button>
          </div>
        </div>
        <div class="col-md-6" id="alert-wrapper">

        </div>
      </div>
    </div>
    <div class="col-md-12" class="submit-wrapper">
      <div class="form-group">
        <Button class="btn btn-success" id="submit">Edit Route</Button>
        <Button class="btn btn-danger" id="delete">Delete Route</Button>
      </div>
    </div>
  </div>



</div>

<script>
  let selectedRouteId = undefined; // Variable to store the selected route ID
  let selectedRouteIds = []; // Variable to store the selected route ID
  let selectedRouteData = {}; // Object to store the data of the selected route
  let parent_id = undefined; // Variable to store the ID of the selected parent route
  let routes = []; // Array to store the routes
  let periods = []; // Array to store the periods
  let unique_ids = []; // Array to store the unique IDs

  // Function: getRoutes
  // Description: Fetches the menu tree data from the server.
  function getRoutes() {
    fetch("http://localhost/soshiane/admin/api/v1/get_menu_tree.php?key=text&type=json")
      .then((res) => res.json())
      .then((res) => {
        routes = res.sort((a, b) => Number(a.unique_id) - Number(b.unique_id));
        setRotuesData(routes);
        createTree(parseMenuData(res));
      })
      .catch(() => {
        alert("Internal Server Error");
      });
  }

  // Function: parseMenuData
  // Description: Parses the menu data and returns a modified array.
  // Parameters:
  // - data: The menu data to be parsed.
  // Returns: The modified array of menu data.
  function parseMenuData(data) {
    return data.map((item) => {
      return {
        id: item.id.toString(),
        text: `${item.unique_id.toString()}-${item.name.toString()}`,
        parent: item.parent.toString() === "0" ? "#" : item.parent.toString(),
      };
    });
  }

  // Function: rootChange
  // Description: Handles the root change event.
  function rootChange(e) {
    const {
      checked
    } = event.target;
    $("#parent_id").css("visibility", checked ? "hidden" : "visible");
  }

  // Function: insertFormData
  // Description: Inserts the form data of the selected route.
  // Parameters:
  // - route_id: The ID of the selected route.
  function insertFormData(route_id) {
    const data = routes.find((i) => i.id.toString() === route_id.toString());
    selectedRouteData = data;
    $("input").each(function() {
      const element = $(this);
      const id = element.attr("id");
      element.val(data[id]);
    });
    $("select").each(function() {
      const element = $(this);
      const id = element.attr("id");
      element.val(data[id]);
    });
    showAlerts();
  }

  // Function: addAlerts
  // Description: Adds an alert to the selected route data.
  function addAlerts() {
    console.log("addAlerts()");
    const content = $("#alert-content");
    if (content.val() === "") return;
    let alerts = JSON.parse(selectedRouteData.alerts || "[]");
    if (!Array.isArray(alerts)) alerts = [];
    alerts.push({
      color: $("#alert-color").val(),
      content: content.val(),
    });
    content.val("");
    console.log({
      alerts
    });
    selectedRouteData.alerts = JSON.stringify(alerts);
    showAlerts();
  }

  // Function: showAlerts
  // Description: Displays the alerts for the selected route.
  function showAlerts() {
    $("#alert-wrapper").empty();
    console.log("showAlerts()");
    console.log({
      selectedRouteData
    });
    if (!selectedRouteData.alerts) return;
    const alerts = JSON.parse(selectedRouteData.alerts);
    alerts.forEach((item) => {
      const alertBox = document.createElement("div");
      alertBox.classList.add("alert", `alert-${item.color}`, "alert-dismissible");
      const closeButton = document.createElement("a");
      closeButton.href = "#";
      closeButton.classList.add("close");
      closeButton.setAttribute("data-dismiss", "alert");
      closeButton.setAttribute("aria-label", "close");
      closeButton.onclick = () => {
        const newAlerts = alerts.filter((i) => {
          return (
            i.content.trim() !== item.content.trim() || item.color !== i.color
          );
        });
        selectedRouteData.alerts = JSON.stringify(newAlerts);
        showAlerts();
      };
      closeButton.innerHTML = "&times;";
      const messageText = document.createTextNode(item.content);
      alertBox.appendChild(closeButton);
      alertBox.appendChild(messageText);

      // Add the alert box to the page
      $("#alert-wrapper").append(alertBox);
    });
  }

  // Function: createTree
  // Description: Creates the tree view for the routes.
  // Parameters:
  // - data: The data to be used for creating the tree view.
  function createTree(data) {
    $("#parent")
      .on("changed.jstree", function(e, data) {
        selectedRouteIds = data.selected;
        selectedRouteId = data.selected[0];
        insertFormData(selectedRouteId);
      })
      .jstree({
        plugins: ["search", "checkbox"],
        core: {
          data: data,
          multiple: true,
        },
      });
    $("#parent_id")
      .on("changed.jstree", function(e, data) {
        parent_id = data.selected[0];
      })
      .jstree({
        plugins: ["search"],
        core: {
          data: data,
          multiple: false,
        },
      });

    $("#search-route").on("change", (e) => {
      $("#parent").jstree(true).search(e.target.value);
    });
    $("#search-parent").on("change", (e) => {
      $("#parent_id").jstree(true).search(e.target.value);
    });
  }

  // Function: setRotuesData
  // Description: Sets the routes data for further use.
  // Parameters:
  // - routes: The routes data to be set.
  function setRotuesData(routes) {
    periods = routes.map((item) => item.period).filter((i) => i !== "");
    periods = periods.reduce((values, item) => {
      if (!values.includes(item)) values.push(item);
      return values;
    }, []);
    unique_ids = routes.map((item) => item.unique_id);
    const periodsData = {
      weekly: 0,
      monthly: 1,
      quarterly: 2,
      annual: 3,
    };
    periods.sort((a, b) => {
      return periodsData[a.toLowerCase()] - periodsData[b.toLowerCase()]
    }).forEach((UID) => {
      const element = document.createElement("li");
      element.onclick = () => $("#period").val(UID);
      element.innerText = UID;
      $("#auto_complete").append(element);
    });
  }

  // Function: editRouteRequest
  // Description: Sends a request to edit the selected route.
  function editRouteRequest() {
    const route = routes.find(i => i.id == selectedRouteId);;
    if (!confirm(`Do you want to edit ${route?.name}`)) return;
    const formData = new FormData();
    const old_unique_id = routes.find(
      (i) => i.id.toString() === selectedRouteId.toString()
    ).unique_id;
    const unique_id = $("#unique_id").val().trim();
    if (unique_ids.includes(unique_id) && old_unique_id !== unique_id) {
      alert("Unique id should be unique");
      return;
    }
    formData.append("action", "edit");
    formData.append("id", selectedRouteId);
    formData.append("name", $("#name").val().trim());
    formData.append("unit", $("#unit").val().trim());
    formData.append("unique_id", unique_id);
    formData.append("alerts", selectedRouteData.alerts);
    const isRoot = $("#parent_checkbox").is(":checked");
    console.log({
      parent_id,
      isRoot
    });
    if (parent_id !== undefined || isRoot)
      formData.append("parent", isRoot ? "0" : parent_id);
    formData.append("period", $("#period").val().trim());
    formData.append("is_active", $("#is_active").val().trim());
    fetch("http://localhost/soshiane/admin/api/v1/routes.php", {
        body: formData,
        method: "POST",
      })
      .then(async (res) => {
        const responseText = await res.text();
        console.log(responseText);
        if (!res.status === 200) throw new Error(responseText);
        alert("Success");
        location.reload();
      })
      .catch((e) => {
        console.log(e);
        alert("Internal Server Error");
      });
  }

  // Function: findChild
  // Description: Finds the child routes of a given route.
  // Parameters:
  // - data: The routes data to search in.
  // - cursor: The ID of the current route.
  // Returns: An array of child routes.
  function findChild(data = routes, cursor = 0) {
    const isDataValid = Array.isArray(data) && data?.length !== 0;
    if (!isDataValid) return [];
    let result = [];
    const children = data.filter((child) => String(child.parent) === String(cursor));
    result = result.concat(children);
    if (children.length > 0) {
      for (const child of children) {
        result = result.concat(findChild(data, child.id));
      }
    }
    return result;
  }

  // Function: deleteRouteRequest
  // Description: Sends a request to delete the selected route.
  function deleteRouteRequest(selectedRoute) {
    let route = routes.find(i => i.id == selectedRoute);
    let routeName = route?.name;
    let period = "Feature";
    if (route?.period !== "") {
      routeName = routes.find(i => i.id == route?.parent).name;
      period = route?.period;
    }
    if (!confirm(`Are you sure to delete ${routeName} - ${period} (${route?.unique_id})?`)) return;
    const formData = new FormData();
    formData.append("action", "delete");
    let ids = findChild(routes, selectedRoute).map((i) => i.id);
    ids.push(selectedRoute);
    formData.append("id", ids.join(","));
    fetch("http://localhost/soshiane/admin/api/v1/routes.php", {
        body: formData,
        method: "POST",
      })
      .then(async (res) => {
        if (res.status === 200) {
          alert("Successfully remove " + routeName);
          location.reload();
        } else alert("Internal Server Error");
      })
      .catch(() => {
        alert("Internal Server Error");
      });
  }

  // Function: bootstrap
  // Description: Initializes the necessary elements and functions.
  function bootstrap() {
    document.getElementById("add-alert").onclick = (e) => {
      addAlerts();
    };
    document.getElementById("submit").onclick = (e) => {
      editRouteRequest();
    };
    document.getElementById("delete").onclick = (e) => {
      if (selectedRouteIds.length > 10) return alert("Out of range: please select 10 route")
      selectedRouteIds.forEach(deleteRouteRequest)
    };
    getRoutes();
  }

  $(() => {
    bootstrap();
  });
</script>

<?php require_once THEMES . 'templates/footer.php'; ?>