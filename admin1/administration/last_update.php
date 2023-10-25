<?php


require_once __DIR__ . '/../maincore.php';
require_once __DIR__ . '/../models/users.php';
require_once THEMES . 'templates/admin_header.php';
pageAccess('LUP');
?>

<style>
    .container {
        width: 100%;
    }

    .row {
        width: 100%;
        margin: 10px;
    }

    .table-wrapper {
        width: 95%;
        margin: 30px 10px;

    }
</style>

<div class="container">
    <div class="row">

        <div class="col-md-12 table-wrapper table-responsive">
            <label>Prediction:</label>
            <table class="table" id="predictions-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Feature</th>
                        <th>W</th>
                        <th>Date Modified</th>
                        <th>M</th>
                        <th>Date Modified</th>
                        <th>Q</th>
                        <th>Date Modified</th>
                        <th>A</th>
                        <th>Date Modified</th>
                    </tr>
                </thead>
                <tbody id="body-predictions">
                </tbody>
            </table>
        </div>
        <div class="col-md-12 table-wrapper table-responsive">
            <label>Evaluation:</label>
            <table class="table" id="evaluation-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Feature</th>
                        <th>W</th>
                        <th>Date Modified</th>
                        <th>M</th>
                        <th>Date Modified</th>
                        <th>Q</th>
                        <th>Date Modified</th>
                        <th>A</th>
                        <th>Date Modified</th>
                    </tr>
                </thead>
                <tbody id="body-evaluation">
                </tbody>
            </table>
        </div>
        <div class="col-md-12 table-wrapper table-responsive">
            <label>Top Ten:</label>
            <table class="table" id="evaluation-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Feature</th>
                        <th>W</th>
                        <th>Date Modified</th>
                        <th>M</th>
                        <th>Date Modified</th>
                        <th>Q</th>
                        <th>Date Modified</th>
                        <th>A</th>
                        <th>Date Modified</th>
                    </tr>
                </thead>
                <tbody id="body-top-ten">
                </tbody>
            </table>
        </div>

    </div>



    <script>

        let routes = [];
        $(() => {
            bootstrap()
        })



        async function getPredictedData() {
            await fetch(`/api/v1/routes_data.php?action=get`)
                .then((res) => res.json())
                .then((res) => {
                    createDataTable(parseData(Object.values(res)), "#body-predictions")
                })
                .catch((error) => {
                    console.log(error);
                    alert("Internal Server Error");
                });
        }
        async function getEvaluation() {
            await fetch(`/api/v1/routes_data.php?action=get_evaluation`)
                .then(async (res) => await res.json())
                .then((res) => {
                    createDataTable(parseData(Object.values(res)), "#body-evaluation")
                })
                .catch((error) => {
                    console.log(error);
                    console.log(error);
                    alert("Internal Server Error");
                });
        }
        async function getTopTen() {
            await fetch(`/api/v1/top_ten.php?action=get_all`)
                .then(async (res) => await res.json())
                .then((res) => {
                    console.log(res);
                    createDataTable(parseTopTen(Object.values(res)), "#body-top-ten")
                })
                .catch((error) => {
                    console.log(error);
                    console.log(error);
                    alert("Internal Server Error");
                });
        }

        function getRoutes() {
            fetch("/api/v1/get_menu_tree.php?key=text&type=json")
                .then((res) => res.json())
                .then((res) => {
                    routes = res;
                    getPredictedData()
                    getEvaluation()
                    getTopTen()
                })
                .catch((error) => {
                    console.log(error);
                    alert("Internal Server Error");
                });
        }


        function parseData(data) {
            const result = {};
            data.forEach(item => {
                const route = routes.find(i => i.unique_id?.toString() === item.unique_id?.toString())
                if (!route) return;
                const parent = findParent(route.parent)
                if (!parent) return;
                if (result[parent.id] === undefined) result[parent.id] = {};
                result[parent.id].Feature = parent.name;
                if (route.period.trim().toLowerCase() === "weekly") {
                    const value = JSON.parse(item.value)
                    result[parent.id].W = String(item.is_real) === "0" ? Object.keys(value).join("/") : value[value.length - 1]?.Title
                    const date = new Date(item.create_time * 1000);
                    const options = { year: 'numeric', month: 'long', day: 'numeric' };
                    const dateString = date.toLocaleString('en-US', options);
                    result[parent.id].WDateModified = dateString;
                    result[parent.id].isActiveW = String(route.is_active) === "1";
                }
                if (route.period.trim().toLowerCase() === "monthly") {
                    const value = JSON.parse(item.value)
                    result[parent.id].M = String(item.is_real) === "0" ? Object.keys(value).join("/") : value[value.length - 1]?.Title
                    const date = new Date(item.create_time * 1000);
                    const options = { year: 'numeric', month: 'long', day: 'numeric' };
                    const dateString = date.toLocaleString('en-US', options);
                    result[parent.id].MDateModified = dateString;
                    result[parent.id].isActiveM = String(route.is_active) === "1";
                }
                if (route.period.trim().toLowerCase() === "quarterly") {
                    const value = JSON.parse(item.value)
                    result[parent.id].Q = String(item.is_real) === "0" ? Object.keys(value).join("/") : value[value.length - 1]?.Title
                    const date = new Date(item.create_time * 1000);
                    const options = { year: 'numeric', month: 'long', day: 'numeric' };
                    const dateString = date.toLocaleString('en-US', options);
                    result[parent.id].QDateModified = dateString;
                    result[parent.id].isActiveQ = String(route.is_active) === "1";
                }
                if (route.period.trim().toLowerCase() === "annual") {
                    const value = JSON.parse(item.value)
                    result[parent.id].A = String(item.is_real) === "0" ? Object.keys(value).join("/") : value[value.length - 1]?.Title
                    const date = new Date(item.create_time * 1000);
                    const options = { year: 'numeric', month: 'long', day: 'numeric' };
                    const dateString = date.toLocaleString('en-US', options);
                    result[parent.id].ADateModified = dateString;
                    result[parent.id].isActiveA = String(route.is_active) === "1";
                }
                result[parent.id].isActive = String(parent.is_active) === "1";
            })
            return result;
        }

        function parseTopTen(data) {
            const result = {};
            data.forEach(item => {
                const route = routes.find(i => i.unique_id?.toString() === item.unique_id?.toString())
                if (!route) return;
                const parent = findParent(route.parent)
                if (!parent) return;
                if (result[parent.id] === undefined) result[parent.id] = {};
                result[parent.id].Feature = parent.name;
                result[parent.id].W = "Weekly"
                if (route.period.trim().toLowerCase() === "weekly") {
                    const date = new Date(item.create_time * 1000);
                    const options = { year: 'numeric', month: 'long', day: 'numeric' };
                    const dateString = date.toLocaleString('en-US', options);
                    result[parent.id].WDateModified = dateString;
                    result[parent.id].isActiveW = String(route.is_active) === "1";
                }
                result[parent.id].M = "Monthly"
                if (route.period.trim().toLowerCase() === "monthly") {
                    const date = new Date(item.create_time * 1000);
                    const options = { year: 'numeric', month: 'long', day: 'numeric' };
                    const dateString = date.toLocaleString('en-US', options);
                    result[parent.id].MDateModified = dateString;
                    result[parent.id].isActiveM = String(route.is_active) === "1";
                }
                result[parent.id].Q = "Quarterly"
                if (route.period.trim().toLowerCase() === "quarterly") {
                    const date = new Date(item.create_time * 1000);
                    const options = { year: 'numeric', month: 'long', day: 'numeric' };
                    const dateString = date.toLocaleString('en-US', options);
                    result[parent.id].QDateModified = dateString;
                    result[parent.id].isActiveQ = String(route.is_active) === "1";
                }
                result[parent.id].A = "Annual"
                if (route.period.trim().toLowerCase() === "annual") {
                    const date = new Date(item.create_time * 1000);
                    const options = { year: 'numeric', month: 'long', day: 'numeric' };
                    const dateString = date.toLocaleString('en-US', options);
                    result[parent.id].ADateModified = dateString;
                    result[parent.id].isActiveA = String(route.is_active) === "1";
                }
            })
            return result;
        }


        function addRowToPredictionTable({
            No = 0,
            Feature = "N/A",
            W = "N/A",
            WDateModified = "N/A",
            M = "N/A",
            MDateModified = "N/A",
            Q = "N/A",
            QDateModified = "N/A",
            A = "N/A",
            ADateModified = "N/A",
            isActiveW = false,
            isActiveM = false,
            isActiveQ = false,
            isActiveA = false,
        }, tableSelector) {
            const tr = document.createElement('tr');
            const NoTag = document.createElement('td')
            NoTag.innerText = No;
            const FeatureTag = document.createElement('td')
            FeatureTag.innerText = Feature;
            NoTag.classList.add("info")
            FeatureTag.classList.add("info")
            const WTag = document.createElement('td')
            WTag.classList.add(isActiveW ? "success" : "danger")
            WTag.innerText = W;
            const WDateModifiedTag = document.createElement('td')
            WDateModifiedTag.classList.add(isActiveW ? "success" : "danger")
            WDateModifiedTag.innerText = WDateModified;
            const MTag = document.createElement('td')
            MTag.classList.add(isActiveM ? "success" : "danger")
            MTag.innerText = M;
            const MDateModifiedTag = document.createElement('td')
            MDateModifiedTag.classList.add(isActiveM ? "success" : "danger")
            MDateModifiedTag.innerText = MDateModified;
            const QTag = document.createElement('td')
            QTag.classList.add(isActiveQ ? "success" : "danger")
            QTag.innerText = Q;
            const QDateModifiedTag = document.createElement('td')
            QDateModifiedTag.classList.add(isActiveQ ? "success" : "danger")
            QDateModifiedTag.innerText = QDateModified;
            const ATag = document.createElement('td')
            ATag.classList.add(isActiveA ? "success" : "danger")
            ATag.innerText = A;
            const ADateModifiedTag = document.createElement('td')
            ADateModifiedTag.classList.add(isActiveA ? "success" : "danger")
            ADateModifiedTag.innerText = ADateModified;
            tr.appendChild(NoTag);
            tr.appendChild(FeatureTag);
            tr.appendChild(WTag);
            tr.appendChild(WDateModifiedTag);
            tr.appendChild(MTag);
            tr.appendChild(MDateModifiedTag);
            tr.appendChild(QTag);
            tr.appendChild(QDateModifiedTag);
            tr.appendChild(ATag);
            tr.appendChild(ADateModifiedTag);
            $(tableSelector).append(tr)
        }




        function findParent(parent) {
            return routes.find(item => item.id?.toString() === parent?.toString())
        }



        function createDataTable(data, tableSelector) {

            Object.values(data).forEach((item, index) => {
                addRowToPredictionTable({ ...item, No: index + 1 }, tableSelector)
            });
        }

        function bootstrap() {
            getRoutes()
        }


    </script>



    <?php require_once THEMES . 'templates/footer.php'; ?>