<?php
require_once __DIR__ . '/../maincore.php';
require_once THEMES . 'templates/admin_header.php';
pageAccess('EX');
#region jstree
// Add jstree to head
add_to_head('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css" />');
add_to_head('<script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/jstree.min.js"></script>');
#endregion jstree
?>

<style>
    .row {
        margin: 25px !important;
        padding: 10px !important;
    }

    button {
        color: white !important;
    }

    .spinner {
        visibility: collapse;
        display: inline-block;
        margin: 0 10px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #3498db;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    td,
    th {
        text-align: center;
        padding: 5px;
    }

    .table-wrapper {
        margin-top: 25px !important;
        padding: 10px !important;
    }

    .container {
        width: 100% !important;
    }
</style>

<div class="container">
    <div class="row">
        <div class="col-md-6">
            <div>
                <label>Search:</label>
                <input type="text" class="form-control" id="search">
            </div>
        </div>
        <div class="col-md-6">
            <div>
                <label>Click button to fetch data: <div class="spinner" id="spinner"></div></label>
                <Button class="form-control btn-success" id="submit">Fetch Data</Button>
            </div>
        </div>
        <div class="col-md-12" id="frmt">

        </div>
        <div class="col-md-12 table-wrapper table-responsive">
            <label>Prediction:</label>
            <table class="table table-bordered" id="prediction-table">
                <thead>
                    <tr>
                        <th>Year</th>
                        <th>Period</th>
                        <th>First Prediction</th>
                        <th>First Value</th>
                        <th>Second Prediction</th>
                        <th>ValueSecond </th>
                        <th>Third Prediction</th>
                        <th>Third Value</th>
                        <th>First Average Accuracy</th>
                        <th>Second Average Accuracy</th>
                        <th>Third Average Accuracy</th>
                        <th>First Confidence Level</th>
                        <th>Second Confidence Level</th>
                        <th>Third Confidence Level</th>
                        <th>Unique Id</th>
                        <th>Upload Time</th>
                    </tr>
                    <!-- <tr>
                        <td>year</td>
                        <td>period</td>
                        <td>
                            202304 = 9 <br> 202305 = 8 <br> 202306 = 9
                        </td>
                        <td>unique_id</td>
                        <td>Confidence Level 1 = 125.0256 <br>Confidence Level 2 = 125.0256 <br>Confidence Level 3 =
                            125.0256</td>
                        <td>Average Accuracy 1 = 125.0256 <br>Average Accuracy 2 = 125.0256 <br>Average Accuracy 3 =
                            125.0256</td>
                        <td>average_accuracy</td>
                        <td>confidence_level</td>
                        <td>6/4/2023, 12:15:19 PM</td>
                    </tr> -->
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <div class="col-md-12 table-wrapper table-responsive">
            <label>Evaluation:</label>
            <table class="table" id="evaluation-table">
                <thead>
                    <tr>
                        <th>year</th>
                        <th>period</th>
                        <th>unique_id</th>
                        <th>time</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>

    </div>
</div>

<script>
    // var dt = new DataTree({
    //         fpath: '/api/v1/get_menu_tree.php',
    //         container: '#frmt',
    //         json: true
    //       });

    let selectedRoutesId = [];
    let routes = [];
    let routesData = [];
    $(() => {
        bootstrap()
    });

    function bootstrap() {
        getRoutes()
        document.getElementById("submit").onclick = async () => {
            resetPage()
            $("#submit").attr('disabled', 'disabled')
            $("#spinner").css("visibility", "visible")
            const features = routes.filter(item => item.period !== "").map(i => String(i.id))
            const requests = selectedRoutesId.filter(item => features.includes(item.toString())).map(selectedId => {
                return getData(selectedId)
            })
            Promise.all(requests)
                .finally(() => {
                    $("#submit").removeAttr('disabled')
                    $("#spinner").css("visibility", "collapse")
                })
        }
    }


    function findParent(parent) {
        return routes.find(item => item.id?.toString() === parent?.toString())
    }


    function getRoutes() {
        fetch("/api/v1/get_menu_tree.php?key=text&type=json")
            .then((res) => res.json())
            .then((res) => {
                routes = res;
                createTree(parseMenuData(res));
            })
            .catch(() => {
                alert("Internal Server Error");
            });
    }
    async function getData(selectedId) {
        await fetch(`/api/v1/routes_data.php?action=get_by_id&id=${selectedId}`)
            .then((res) => res.json())
            .then((res) => {
                if (res.predictValue)
                    addRowPrediction(res.predictValue)
                if (res.actualValue)
                    addRowEvaluation(res.actualValue)
            })
            .catch(() => {
                alert("Internal Server Error");
            });
    }
    /**
     * @typedef {Object} PredictionData
     * @property {number} id - The ID of the data point.
     * @property {number} year - The year of the data point.
     * @property {string} period - The period of the data point.
     * @property {string} value - The value of the data point, represented as a JSON string.
     * @property {string} more_info - Additional information about the data point, represented as a JSON string.
     * @property {string} unique_id - The unique ID of the data point.
     * @property {number} is_real - Indicates whether the data point is real or not.
     * @property {number} average_accuracy - The average accuracy of the data point.
     * @property {number} confidence_level - The confidence level of the data point.
     * @property {number} create_time - The creation time of the data point.
     */

    /**
     * @param {PredictionData} data
     */
    function addRowPrediction(data) {

        function parseData() {
            let value = JSON.parse(data.value);
            const more_info = JSON.parse(data.more_info);
            const titles = Object.keys(value)
            const values = Object.values(value)
            return {
                year: data.year,
                period: data.period,
                title1: titles[0] || "",
                value1: values[0] || "",
                title2: titles[1] || "",
                value2: values[1] || "",
                title3: titles[2] || "",
                value3: values[2] || "",
                AverageAccuracy1: more_info?.AVG1 || "",
                AverageAccuracy2: more_info?.AVG2 || "",
                AverageAccuracy3: more_info?.AVG3 || "",
                ConfidenceLevel1: more_info?.CL1 || "",
                ConfidenceLevel2: more_info?.CL2 || "",
                ConfidenceLevel3: more_info?.CL3 || "",
                unique_id: data.unique_id,
                time: new Date(data.create_time * 1000).toLocaleString()
            }
        }
        const tr = document.createElement("tr");
        const predictionValue = parseData();
        for (let key in predictionValue) {
            const tag = document.createElement("td")
            tag.innerHTML = predictionValue[key];
            tr.appendChild(tag)
        }
        document.getElementById("prediction-table").getElementsByTagName("tbody")[0].appendChild(tr);
    }

    /**
 * @param {PredictionData} data
 */
    function addRowEvaluation(data) {

        function parseData() {
            return {
                year: data.year,
                period: data.period,
                unique_id: data.unique_id,
                time: new Date(data.create_time * 1000).toLocaleString()
            }
        }
        const tr = document.createElement("tr");
        const predictionValue = parseData();
        for (let key in predictionValue) {
            const tag = document.createElement("td")
            tag.innerHTML = predictionValue[key];
            tr.appendChild(tag)
        }
        document.getElementById("evaluation-table").getElementsByTagName("tbody")[0].appendChild(tr);
    }

    function parseMenuData(data) {
        return data.map((item) => {
            return {
                id: item.id.toString(),
                text: `${item.unique_id.toString()}-${item.name.toString()}`,
                parent: item.parent.toString() === "0" ? "#" : item.parent.toString(),
            };
        });
    }


    function resetPage() {
        document.getElementById("evaluation-table").getElementsByTagName("tbody")[0].innerHTML = "";
        document.getElementById("prediction-table").getElementsByTagName("tbody")[0].innerHTML = "";
    }
    function createTree(data) {
        console.log("create tree");
        $('#frmt')
            .on('changed.jstree', function (e, data) {
                selectedRoutesId = data.selected;
            })
            .jstree({
                "plugins": ["wholerow", "search", "checkbox"],
                'core': {
                    'data': data
                }
            })

        $("#search").on("change", (e) => {
            $('#frmt')
                .jstree(true).search(e.target.value)
        })
    }
</script>

<?php require_once THEMES . 'templates/footer.php'; ?>