<?php
require_once __DIR__ . '/../maincore.php';
require_once THEMES . 'templates/admin_header.php';
pageAccess('CS');
#region jstree
// Add jstree to head
add_to_head('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css" />');
add_to_head('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.5.0-1/css/all.min.css"/>');
add_to_head('<script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/jstree.min.js"></script>');
#endregion jstree
?>

<style>
  .row {
    margin: 25px !important;
    padding: 10px !important;
  }

  .container {
    width: 100%;
  }

  .fa-check-circle {
    color: green;
  }

  .fa-times-circle {
    color: red;
  }

  .fa-dot-circle {
    color: yellow;
  }
</style>

<div class="container">
  <div class="row">
    <div class="col-md-8">
      <div>
        <label for="search">Search:</label>
        <input type="text" class="form-control" id="search">
      </div>
      <div id="frmt"></div>
    </div>
    <div class="col-md-4" id="buttons-area">
      <button id="Active" class="btn btn-success btn-block w-80">Active</button>
      <button id="Deactive" class="btn btn-danger btn-block w-80">Deactive</button>
    </div>

  </div>
</div>

<script>
  // var dt = new DataTree({
  //         fpath: '/api/v1/get_menu_tree.php',
  //         container: '#frmt',
  //         json: true
  //       });

  let selectedRoutesId = []; // Array to store the selected route IDs
  let routes = []; // Array to store the routes data

  $(() => {
    bootstrap();
  });

  // Function: bootstrap
  // Description: Initializes the page by setting up event listeners and fetching routes data.
  function bootstrap() {
    document.getElementById("Active").onclick = () => sendRequest(true);
    document.getElementById("Deactive").onclick = () => sendRequest(false);
    getRoutes();
  }

  // Function: sendRequest
  // Description: Sends a request to change the status of selected routes.
  // Parameters:
  // - status: The status to be set (true for active, false for inactive).
  function sendRequest(status) {
    const url = "/api/v1/routes.php"; // API endpoint URL
    const formData = new FormData(); // Create a new FormData object
    formData.append("action", "change_status"); // Add "action" parameter with value "change_status"
    formData.append("status", status ? 1 : 0); // Add "status" parameter with value based on the "status" parameter passed to the function
    formData.append("routes", selectedRoutesId.join(",")); // Add "routes" parameter with comma-separated string of selectedRoutesId values

    if (confirm("Are you sure?")) { // Display a confirmation dialog
      fetch(url, {
        method: "POST", // Send a POST request
        body: formData, // Set the request body as the FormData object
      })
        .then(r => r.json())
        .then(async (res) => { // Handle the response
          if (res.code === 200) { // If the response status is 200 (OK)
            alert("success"); // Show a success message
            window.location.reload(); // Reload the page
          } else {
            alert("Internal Server Error"); // Show an error message
          }
        })
        .catch((e) => {
          console.log(e);
          alert("Internal Server Error"); // Show an error message
        });
    }
  }


  // Function: getRoutes
  // Description: Retrieves the routes data from the server.
  function getRoutes() {
    fetch("/api/v1/get_menu_tree.php?key=text&type=json")
      .then((res) => res.json())
      .then((res) => {
        routes = res.filter(i => Boolean(i.name)).sort((a, b) => Number(a.unique_id) - Number(b.unique_id));
        createButtons();
        createTree(parseMenuData(res));
      })
      .catch((e) => {
        console.log(e);
        alert("Internal Server Error");
      });
  }

  // Function: createButtons
  // Description: Creates buttons for each unique period and sets up their click event.
  function createButtons() {
    const periods = [...new Set(routes.map((i) => i.period))].filter((i) => i !== "");
    const sorted = {
      weekly: 0,
      monthly: 1,
      quarterly: 2,
      annual: 3,
    }
    
    
    
    periods.sort((a, b) => sorted[a.toLocaleLowerCase()] - sorted[b.toLocaleLowerCase()]).forEach(createAPeriodButton);
  }


  function createAPeriodButton(period) {
    const button = document.createElement("button");
    button.setAttribute("class", "btn btn-primary btn-block w-80");
    button.innerText = period;
    button.onclick = function () { onSelectAPeriod(period) };
    document.getElementById("buttons-area").appendChild(button);
  }

  function onSelectAPeriod(period) {
    const targetRoutes = routes.filter((i) => i.period === period);
    const isAllSelected = targetRoutes.every((i) =>
      selectedRoutesId.map(String).includes(String(i.id))
    );
    if (isAllSelected) {
      $("#frmt").jstree("deselect_node", routes.filter((i) => i.period?.toString().trim() === period?.toString().trim()).map((i) => i.id));
    } else {
      const select_nodes = targetRoutes.map((i) => String(i.id).trim());
      $("#frmt").jstree("select_node", select_nodes);
    }
  }


  function getAllChildren1(parentId) {
    const nodeList = routes;
    const children = [];
    for (let i = 0; i < nodeList.length; i++) {
      const node = nodeList[i];
      if (node.parent === parentId) {
        children.push(node);
        const grandchildren = getAllChildren(node.id);
        children.push(...grandchildren);
      }
    }
    return children;
  }
  function getAllChildren(parentId, processedNodes = {}) {
    const children = [];
    for (let i = 0; i < routes.length; i++) {
      const node = routes[i];
      if (String(node.parent) === String(parentId) && !processedNodes[node.id]) {
        children.push(node);
        processedNodes[node.id] = true;
        const grandchildren = getAllChildren(node.id, processedNodes);
        children.push(...grandchildren);
      }
    }
    return children;
  }

  // Function: parseMenuData
  // Description: Parses the menu data received from the server.
  // Parameters:
  // - data: The menu data to be parsed.
  // Returns: An array of parsed menu items.
  function parseMenuData(data) {
    return data.map((item) => {
      if(item.unique_id == "111111") {
        debugger
      }
      let children = getAllChildren(String(item.id));
      children = children.filter(item => {
        return item.period !== ""
      });
      let isUnchecked = String(item.is_active) === "0";
      let isChecked = String(item.is_active) === "1";

      if (children.length > 0) {
        isUnchecked = children.every(i => {
          return String(i.is_active) === "0"
        });
        isChecked = children.every(i => {
          return String(i.is_active) === "1"
        }) && !isUnchecked;
      }
      const isUnknown = !isChecked && !isUnchecked;
      const icon = `${isChecked && "fa fa-check-circle"} ${isUnchecked && "fa fa-times-circle"} ${isUnknown && "fa fa-dot-circle"}`;

      return {
        id: item.id.toString(),
        icon, //isChecked ? "" : "",
        text: `${item.unique_id.toString()}-${item.name.toString()}`,
        parent: item.parent.toString() === "0" ? "#" : item.parent.toString(),
      };
    });
  }

  // Function: createTree
  // Description: Creates the tree view using the parsed menu data and sets up event listeners.
  // Parameters:
  // - data: The parsed menu data.
  function createTree(data) {
    $("#frmt")
      .on("changed.jstree", function (e, data) {
        selectedRoutesId = data.selected;
      })
      .jstree({
        plugins: ["wholerow", "checkbox", "search"],
        core: {
          data: data,
        },
      });

    $("#search").on("change", (e) => {
      $("#frmt").jstree(true).search(e.target.value);
    });
  }

</script>

<?php require_once THEMES . 'templates/footer.php'; ?>