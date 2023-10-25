<?php
require_once __DIR__ . '/../maincore.php';
require_once __DIR__ . '/../models/users.php';
require_once THEMES . 'templates/admin_header.php';
pageAccess("ADD");
#region jstree
// Add jstree to head
add_to_head('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css" />');
add_to_head('<script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/jstree.min.js"></script>');
add_to_head('<script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.4.1/papaparse.min.js"></script>');
#endregion jstree
?>

<style>
    .container {
        padding: 10px;
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
        <div class="col-md-12" class="parent-selector-wrapper">
            <label for="search">Search:</label>
            <input style="width: 50%" type="type" id="search" class="form-control">
            <p>Route Parent:</p>
            <div id="parent"></div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Route Title<sup>*</sup>:</label>
                <input type="text" class="form-control" placeholder="Route Title" id="title" />
            </div>
            <div class="form-group">
                <label>Unique Id<sup>*</sup>:</label>
                <input type="number" class="form-control" placeholder="Unique Id" id="unique_id" />
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
        <div class="col-md-12" class="submit-wrapper">
            <div class="form-group">
                <Button class="btn btn-success" id="submit">Add Route</Button>
            </div>
        </div>
        <div class="col-md-4" class="submit-wrapper">
            <div class="form-group">
                <label for="file">File input</label>
                <input type="file" name="file" accept="text/csv" id="file" class="form-control">
                <p id="error-label" class="text-danger text-hide">File is invalid</p>
            </div>
            <div class="form-group">
                <Button class="btn btn-success" id="import">Import Route</Button>
            </div>
            <div class="form-group">
                <Button class="btn btn-success" id="export">Export Routes</Button>
            </div>
        </div>
    </div>



</div>

<script>

    console.clear();
    class AddRoutePage {
        #data = []; // Private property to store the data
        get data() { // Getter for the data property
            return this.#data;
        };
        set data(input) { // Setter for the data property
            const csv = input.split("\n"); // Splitting the input string into an array of lines
            csv[0] = csv[0].toUpperCase(); //Converting the first line to uppercase
            // Parsing the CSV content using Papa.parse library with headers
            const csvContent = Papa.parse(csv.join("\n"), {
                header: true
            }).data;
            // Storing the parsed data after further processing using the routeParse method
            this.#data = this.routeParse(csvContent);
            console.log(this.#data)
        }
        isFileValid = false; // Initializing a public property to indicate file validity
        fileName = ""; // Initializing a public property to store the file name

        // Constants to define keys for specific data fields in the CSV
        IdKey = "Id".toUpperCase();
        TitleKey = "name".toUpperCase();
        PeriodKey = "Period".toUpperCase();
        UniqueIdKey = "Unique_Id".toUpperCase();
        UnitKey = "Unit".toUpperCase();
        StatusKey = "is_active".toUpperCase();
        ParentKey = "Parent".toUpperCase();

        // Method to parse and transform the CSV data into a desired format
        routeParse(data) {
            return data.map(item => {
                return {
                    id: item[this.IdKey], // Extracting the name field from the CSV row
                    name: item[this.TitleKey], // Extracting the name field from the CSV row
                    unique_id: item[this.UniqueIdKey], // Extracting the unique_id field
                    parent: item[this.ParentKey], // Extracting the parent field
                    unit: item[this.UnitKey], // Extracting the unit field
                    period: item[this.PeriodKey], // Extracting the period field
                    is_active: item[this.StatusKey], // Extracting the is_active field
                    is_pendding: 1 // Setting a default value of 1 for the is_pending field
                }
            });
        }
    }

    let selectedRouteId = undefined;
    /**@type {Array<string>} */
    let periods = [];
    /**@type {Array<string>} */
    let unique_ids = [];
    const page = new AddRoutePage();



    $(() => {
        bootstrap()
    });

    // Function: readFile
    // Description: Reads the contents of a file using FileReader API.
    // Parameters:
    // - file: The file object to be read.
    // Returns: A Promise that resolves with the file contents as a string.
    /**@returns {Promise<string>} */
    function readFile(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => {
                resolve(reader.result.toString());
            };
            reader.onerror = reject;
            reader.readAsText(file);
        });
    }


    // Function: sendAddRouteRequest
    // Description: Sends a request to create a new route.
    // Parameters:
    // - name: The name of the route.
    // - unique_id: The unique identifier of the route.
    // - parent: The parent route (optional, default is 0).
    // - unit: The unit of the route (optional, default is an empty string).
    // - period: The period of the route (optional, default is an empty string).
    // - is_active: The active status of the route (optional, default is 1).
    //   (Note: the parameters with default values are optional)
    function sendAddRouteRequest({
        name,
        unique_id,
        parent = 0,
        unit = "",
        period = "",
        is_active = 1,
    }) {
        name = name?.trim() || name;
        is_active = is_active?.trim() || is_active;
        period = period?.trim() || period;
        unit = unit?.trim() || unit;
        unique_id = unique_id?.trim() || unique_id;
        if (!name) {
            alert("Route title is require");
            return;
        };
        if (!unique_id) {
            alert("Unique id is require");
            return;
        };
        if (unique_ids.includes(unique_id)) {
            alert("Unique id should be unique");
            return;
        };
        const url = '/api/v1/routes.php';
        const formData = new FormData();
        formData.append("action", "create");

        formData.append("name", name);
        formData.append("unique_id", unique_id);
        formData.append("parent", parent);
        formData.append("unit", unit);
        formData.append("period", period);
        formData.append("is_active", is_active);
        formData.append("is_pendding", 0);


        fetch(url, {
                method: "POST",
                body: formData
            })
            .then(async (res) => {
                const response = await res.json();
                if (res.status !== 200) {
                    alert("Internal Server Error")
                    throw new Error(response)
                }
                /**
                 * If you want to ask the user before reloading the page,
                 * please uncomment this code
                 * and comment location.reload();
                 */
                else {
                    alert("Success");
                    location.reload();
                }
                // const reload = confirm("Do you want to reload page?")
                // if (reload) location.reload();
                // else {
                //     $("input").each(function() {
                //         $(this).val("")
                //     })
                //     $("select").each(function() {
                //         $(this).val("")
                //     })
                // }
            })
            .catch((err) => {
                console.log(err);
                alert("Internal Server Error")
            });
    }

    // Function: setRoutesData
    // Description: Sets the routes data and populates the periods and unique_ids arrays.
    // Parameters:
    // - routes: An array of routes data.
    function setRotuesData(routes) {
        periods = routes.map(item => item.period).filter(i => i !== "")
        periods = periods.reduce((values, item) => {
            if (!values.includes(item)) values.push(item)
            return values
        }, [])
        unique_ids = routes.map(item => item.unique_id);
        const periodsData = {
            weekly: 0,
            monthly: 1,
            quarterly: 2,
            annual: 3,
        };
        periods.sort((a, b) => {
            return periodsData[a.toLowerCase()] - periodsData[b.toLowerCase()]
        }).forEach(UID => {
            const element = document.createElement("li");
            element.onclick = () => $("#period").val(UID);
            element.innerText = UID;
            $("#auto_complete").append(element)

        })
    }


    // Function: getRoutes
    // Description: Retrieves the routes data from the server.
    function getRoutes() {
        fetch("/api/v1/get_menu_tree.php?key=text&type=json")
            .then(async (res) => {
                return (await res.json()).sort((a, b) => Number(a.unique_id) - Number(b.unique_id));
            })
            .then(res => {
                console.log(res);
                console.log(parseMenuData(res));
                document.getElementById("export").onclick = () => {
                    saveAsCSV(res.map((item, index, routes) => {
                        item.parent = routes.find(i => i.id === item.parent)?.unique_id || 0;
                        return item;
                    }))
                }
                createTree(parseMenuData(res));
                setRotuesData(res)
            })
            .catch((err) => {
                console.log(err)
                alert("Internal Server Error")
            })
    }

    function convertToCSV(data) {
        if (data.length === 0 || !Array.isArray(data)) return "";
        var csvOptions = {
            fields: Object.values(data[0]), // column headers
            delimiter: "," // field delimiter
        };

        // Convert the array of objects to CSV using Papa.unparse()
        return Papa.unparse(data, csvOptions);
    }


    function saveAsCSV(data = [], name = 'Routes.csv') {
        const parsedData = convertToCSV(data)
        const downloadLink = document.createElement('a');
        console.log(parsedData);
        downloadLink.setAttribute('href', 'data:text/csv;charset=utf-8,' + encodeURIComponent(parsedData));
        downloadLink.setAttribute('download', name);
        document.body.appendChild(downloadLink);
        downloadLink.click();
    }


    // Function: parseMenuData
    // Description: Parses the menu data received from the server.
    // Parameters:
    // - data: The menu data to be parsed.
    // Returns: An array of parsed menu items.
    function parseMenuData(data) {
        return data.map(item => {
            return {
                id: item.id.toString(),
                text: `${item.unique_id.toString()}-${item.name.toString()}`,
                parent: item.parent.toString() === "0" ? "#" : item.parent.toString()
            }
        })
    }


    // Function: createTree
    // Description: Creates a tree structure using the parsed menu data.
    // Parameters:
    // - data: The parsed menu data.
    function createTree(data) {
        $('#parent')
            .on('changed.jstree', function(e, data) {
                selectedRouteId = data.selected[0];
            })
            .jstree({
                "plugins": ["search"],
                'core': {
                    'data': data,
                    "multiple": false,
                }
            })
        $("#search").on("change", (e) => {
            $("#parent").jstree(true).search(e.target.value)
        })
    }


    // Function: bootstrap
    // Description: Initializes the page by getting the routes data and adding event listeners.
    function bootstrap() {
        getRoutes()
        document.getElementById("file").addEventListener('change', async function(event) {
            console.log("file Changed");
            const file = event.target.files[0];
            try {
                if (file.type !== "text/csv" && file.type !== "application/vnd.ms-excel") throw new Error("invalid file type")
                const rawFile = (await readFile(file)).trim()
                page.isFileValid = true;
                page.data = rawFile;
                page.fileName = file.name;
            } catch (error) {
                page.isFileValid = false;
                console.error(error);
                alert(error.message)
            }
        });
        document.getElementById("submit").onclick = (e) => {
            const name = document.getElementById("title").value;
            const is_active = document.getElementById("is_active").value;
            const period = document.getElementById("period").value;
            const unit = document.getElementById("unit").value;
            const unique_id = document.getElementById("unique_id").value;
            sendAddRouteRequest({
                name,
                is_active,
                period,
                unit,
                parent: selectedRouteId,
                unique_id
            })
        }
        document.getElementById("import").onclick = sendImportRequest;
    }


    // Function: sendImportRequest
    // Description: Sends a request to import route data.
    function sendImportRequest() {
        const url = '/api/v1/routes.php';
        console.log("sendImportRequest")
        const formData = new FormData();
        formData.append("action", "import");
        for (var i = 0; i < page.data.length; i++) {
            formData.append("data[]", JSON.stringify(page.data[i]));
        }
        console.log({
            page
        })
        fetch(url, {
                method: "POST",
                body: formData
            })
            .then(async (res) => {
                console.log(res)
                const responseText = await res.text();
                try {
                    if (res.status === 204) {
                        alert("File is empty")
                        return;
                    }
                    const response = JSON.parse(responseText);
                    if (response.status !== 200) {
                        alert(response.message || "Failed to import routes")
                    } else {
                        alert("Success")
                        location.reload();
                    }
                } catch (error) {
                    console.log(error)
                    alert("Failed to import routes")
                }
            })
            .catch((err) => {
                console.log(err);
                alert("Internal Server Error")
            });
    }
</script>

<?php require_once THEMES . 'templates/footer.php'; ?>