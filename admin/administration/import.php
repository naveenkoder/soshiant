<?php
require_once __DIR__ . '/../maincore.php';
require_once __DIR__ . '/../models/users.php';
require_once THEMES . 'templates/admin_header.php';
pageAccess("IMP");
#region jstree
// Add jstree to head
add_to_head('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css" />');
add_to_head('<script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/jstree.min.js"></script>');
add_to_head('<script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/jstree.min.js"></script>');
add_to_head('<script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.4.1/papaparse.min.js"></script>');
#endregion jstree
?>

<style>
    .container {
        width: 100%;
        padding: 10px
    }

    .buttons-wrapper {
        margin-top: 37px;
    }

    .buttons-group Button {
        margin-bottom: 10px
    }

    input,
    select {
        margin: 5px;
    }

    #progress-wrapper {
        visibility: collapse;
    }
</style>

<div class="container">
    <div class="row">
        <div class="col-md-12" id="progress-wrapper">
            <div class="row">
                <!-- <div class="col-md-3"></div> -->
                <div class="col-md-6">
                    <span>
                        Uploaded:
                    </span>
                    <br>
                    <progress id="progress" value="32" max="100"> 32% </progress>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="file">File input</label>
                <input type="file" multiple name="file" accept="text/csv" id="file" class="form-control">
                <p id="error-label" class="text-danger text-hide">File is invalid</p>
            </div>
        </div>
        <div class="col-md-7 buttons-wrapper">
            <div class="container">
                <div class="row">
                    <div class="form-group buttons-group">
                        <div class="col-md-12">
                            <Button class="btn btn-success" disabled id="submit">Import data from file</Button>
                        </div>
                        <div class="col-md-12">
                            <Button class="btn btn-info"
                                onclick="downloadCSV('TIME,ACTUALVALUE,ACTUALVALUEWITHNOISE,PREDICTEDVALUE1,PREDICTEDVALUE2,PREDICTEDVALUE3,TITLE', 'filename-[unique_id]-avg-[average_accuracy]-cl-[confidence_level]')">
                                Sample .csv format for evaluation
                            </Button>
                        </div>
                        <div class="col-md-12">
                            <Button class="btn btn-info"
                                onclick="downloadCSV('UNIQUE_ID,VALUE1,VALUE2,VALUE3,TITLE1,TITLE2,TITLE3,CL1,CL2,CL3,AVG1,AVG2,AVG3', '[year]-[period]-filename')">
                                Sample .csv format for Prediction + Confidence/avg
                            </Button>
                        </div>
                        <div class="col-md-12">
                            <Button class="btn btn-info" onclick="downloadCSV('UNIQUE_ID,AVERAGE', 'average-file')">
                                Sample .csv format for average of targets
                            </Button>
                        </div>
                        <div class="col-md-12">
                            <mark>
                                Values in brackets, such as [unique_id], are required and should be included in the
                                filename.
                            </mark>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-11">
                    <div id="csvTable" class="table-responsive"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
	console.clear();
    class ImportPageProperties {

        timeKey = "time".toUpperCase();
        ActualValueKey = "ACTUALVALUE".toUpperCase();
        ActualValueWithNoiseKey = "ACTUALVALUEWITHNOISE".toUpperCase();
        PredictedValue1Key = "PREDICTEDVALUE1".toUpperCase();
        PredictedValue2Key = "PREDICTEDVALUE2".toUpperCase();
        PredictedValue3Key = "PREDICTEDVALUE3".toUpperCase();
        AccuracyKey = "Accuracy".toUpperCase();
        TitleKey = "Title".toUpperCase();
        UniqueIdKey = "Unique_ID".toUpperCase();
        Value1Key = "Value1".toUpperCase();
        Value2Key = "Value2".toUpperCase();
        Value3Key = "Value3".toUpperCase();
        Title1Key = "Title1".toUpperCase();
        Title2Key = "Title2".toUpperCase();
        Title3Key = "Title3".toUpperCase();

        CL1Key = "CL1".toUpperCase();
        CL2Key = "CL2".toUpperCase();
        CL3Key = "CL3".toUpperCase();
        AVG1Key = "AVG1".toUpperCase();
        AVG2Key = "AVG2".toUpperCase();
        AVG3Key = "AVG3".toUpperCase();
        #finished = 0;
        get finished() {
            return this.#finished;
        };
        set finished(value) {
            this.#finished = value;
            $("#progress").attr("value", this.#finished)
        };

        get requestCount() {
            return page.data.length
        };
        #isFileValid = [];
        #data = [];
        #fileName = []
        get fileName() {
            return this.#fileName
        }
        set fileName(value) {
            this.#fileName.push(value)
        }

        clearData() {
            this.#data = [];
            this.#fileName = [];
            this.#isFileValid = [];
            const csvTable = document.getElementById('csvTable');
            csvTable.innerHTML = "";
        }
        get isFileValid() {
            return this.#isFileValid;
        }
        set isFileValid(value) {
            this.#isFileValid.push(value);
            if (this.#isFileValid.every(Boolean)) {
                $("#error-label").addClass("text-hide")
                $("#submit").removeAttr("disabled")

            } else {
                const csvTable = document.getElementById('csvTable');
                csvTable.innerHTML = "";
                $("#error-label").removeClass("text-hide")
                $("#submit").attr("disabled", "disabled")
            }

        }
        get data() {
            return this.#data;
        }
        set data(value) {
            if (value === "") return
            const rows = value.split("\n")
            rows[0] = rows[0].toUpperCase().replace(/\s/g, "");
            this.#data.push(rows.join("\n"));
            const csvTable = document.getElementById('csvTable');
            csvTable.innerHTML = "";
            page.data.forEach(data => {
                csvTable.appendChild(csvToTable(data));
            })
        }


        predictParser(data, index) {
            const fileName = this.fileName[index].replace(".csv", "").split("-");
            const Title1 = data[this.Title1Key];
            const Title2 = data[this.Title2Key];
            const Title3 = data[this.Title3Key];

            const result = {
                unique_id: data[this.UniqueIdKey],
                value: JSON.stringify({
                    [Title1]: Number(data[this.Value1Key]),
                    [Title2]: Number(data[this.Value2Key]),
                    [Title3]: Number(data[this.Value3Key]),
                }),
                more_info: JSON.stringify({
                    [this.CL1Key]: data[this.CL1Key],
                    [this.CL2Key]: data[this.CL2Key],
                    [this.CL3Key]: data[this.CL3Key],
                    [this.AVG1Key]: data[this.AVG1Key],
                    [this.AVG2Key]: data[this.AVG2Key],
                    [this.AVG3Key]: data[this.AVG3Key],
                }),
                is_real: 0,
                year: fileName[0],
                period: fileName[1],
            };

            return JSON.stringify(result)
        }
        AverageParser(data, index) {
            // console.log(data);
            return JSON.stringify({
                unique_id: data["UNIQUE_ID"],
                average: data["AVERAGE"],
            })
        }
        /**@param {array} data */
        actualValueParser(data, index) {
            const fileName = this.fileName[index].replace(".csv", "").split("-");
            const records = data.map(item => {
                return {
                    time: item[this.timeKey],
                    Actual_value: item[this.ActualValueKey],
                    Actual_value_with_noise: item[this.ActualValueWithNoiseKey],
                    Predicted_value_1: item[this.PredictedValue1Key],
                    Predicted_value_2: item[this.PredictedValue2Key],
                    Predicted_value_3: item[this.PredictedValue3Key],
                    Title: item[this.TitleKey]?.trim(),
                }
            })
            const time = records[records.length - 1].time.toString();
            const year = time.substring(0, 4);
            const period = time.substring(4);
            const value = JSON.stringify(records);
            const result = {
                value: value,
                unique_id: fileName[1],
                average_accuracy: fileName[3],
                confidence_level: fileName[5],
                year,
                period,
                is_real: 1,
            }
            //console.log(result);
            return JSON.stringify(result)
        }

        autoParser(data, index) {
            if (this.dataType(data) === "Prediction")
			{
				
				console.log("predictParser");
                return this.predictParser(data, index)
			}
            else if (this.dataType(data) === "Average"){
				
				console.log("AverageParser");
				return this.AverageParser(data, index)
			}
            else
			{
				//console.log(data);
				//console.log(index);
				
				console.log("actualValueParser");
				//console.log(this.actualValueParser(data, index));
				return this.actualValueParser(data, index)
			}
        }
        /**@returns {"Prediction" | "ActualValue" | "Average"} */
        dataType(data) {
            if ((Array.isArray(data) && data[0][this.Value1Key] !== undefined) || data[this.UniqueIdKey] !== undefined) return "Prediction"
            else if (Array.isArray(data) && data[0]["AVERAGE"] !== undefined) return "Average"
            else return "ActualValue"
        }
        /**@returns {"Prediction" | "ActualValue" | "Average"} */
        static dataType(data) {
            if ((Array.isArray(data) && data[0][this.Value1Key] !== undefined) || data[this.UniqueIdKey] !== undefined) return "Prediction"
            else if (Array.isArray(data) && data[0]["AVERAGE"] !== undefined) return "Average"
            else return "ActualValue"
        }
    }

    class FormatValidator {
        // 

        static isNumber(value) {
            const convertToNumber = Number(value)
            const isNaN = Number.isNaN(convertToNumber)
            return !isNaN;
        }

        /**
         * @param {string} header
         */
        static isActualValue(header, content) {
            const items = ["TIME", "ACTUALVALUE", "ACTUALVALUEWITHNOISE", "PREDICTEDVALUE1", "PREDICTEDVALUE2", "PREDICTEDVALUE3", "TITLE"];
            const isHeaderValid = this.validator(header, items);
            return isHeaderValid
        }
        /**
         * @param {string} header
         */
        static isPrediction(header) {
            const items = ["UNIQUE_ID", "VALUE1", "VALUE2", "VALUE3", "TITLE1", "TITLE2", "TITLE3", "CL1", "CL2", "CL3", "AVG1", "AVG2", "AVG3"];
            return this.validator(header, items)
        }

        static isActualValueFileNameValid(rawFileName) {
            const fileName = rawFileName.replace(".csv", "").split("-");
            const unique_id = Number(fileName[1]);
            const average_accuracy = Number(fileName[3]);
            const confidence_level = Number(fileName[5]);
            return !(Number.isNaN(unique_id) || Number.isNaN(average_accuracy) || Number.isNaN(confidence_level))
        }
        static isPredictionFileNameValid(rawFileName) {
            const fileName = rawFileName.replace(".csv", "").split("-");
            const year = Number(fileName[0]);
            const isPeriodIsCorrect = fileName[1]?.length === 2;
            const period = Number(fileName[1]);
            return !(Number.isNaN(year) || Number.isNaN(period) || !isPeriodIsCorrect)
        }
        /**
         * @param {string} header
         */
        static isAverage(header) {
            const items = ["UNIQUE_ID", "AVERAGE"];
            return this.validator(header, items)
        }

        static isValid(header, content, filename) {
            const isActualValueContentValid = this.isActualValue(header, content)
            const isActualValueFileNameValid = this.isActualValueFileNameValid(filename);
            const isPredictionContentValid = this.isPrediction(header)
            const isPredictionFileNameValid = this.isPredictionFileNameValid(filename)
            const isAverageContentValid = this.isAverage(header);
            if (isActualValueContentValid) {
                return {
                    content: isActualValueContentValid,
                    filename: isActualValueFileNameValid
                }
            }
            else if (isPredictionContentValid) {
                return {
                    content: isPredictionContentValid,
                    filename: isPredictionFileNameValid
                }
            }
            else if (isAverageContentValid) {
                return {
                    content: isAverageContentValid,
                    filename: true
                }
            }
            else {
                return {
                    content: false,
                    filename: false,
                };
            }
        }

        /**
         * @param {string} header
         * @param {string[]} items
         * 
         */
        static validator(header, items) {
            // const h = header.split(",").map(i => i.replace(/\s/g, "").replace(/\r/g, "").replace(/\t/g, "").toUpperCase())
            // h.every(i => {
            //     const res = items.includes(i)
            //     if (!res) console.log({
            //         i,
            //         items,
            //         h
            //     });
            //     return res;
            // })
            return header.split(",").map(i => i.replace(/\s/g, "").replace(/\r/g, "").replace(/\t/g, "").toUpperCase()).every(i => items.includes(i))
        }
    }

    function csvToTable(csvText, upperCase = true) {
        const [data, headers] = csvToArray(csvText, upperCase);
        // console.log({
        //     data,
        //     headers
        // });
        const table = document.createElement('table');
        $(table).addClass("table")
            .addClass("table-responsive")
            .addClass("table-striped")

        // Create table header
        const headerRow = table.insertRow();
        for (let i = 0; i < headers.length; i++) {
            const headerCell = document.createElement('th');
            headerCell.textContent = headers[i];
            headerRow.appendChild(headerCell);
        }

        // Create table rows
        for (let i = 0; i < data.length; i++) {
            const dataRow = table.insertRow();
            for (let j = 0; j < headers.length; j++) {
                const dataCell = dataRow.insertCell();
                dataCell.textContent = data[i][headers[j]];
            }
        }

        return table;
    };

    function bootstrap() {
        $("#submit").on("click", importRequest)

        // getRoutes()
        const fileInput = document.querySelector('input[type="file"]');
        fileInput.addEventListener('change', async (event) => {
            page.clearData()
            // const file = event.target.files[0];
            let isFailed = false;
            $("#submit").attr("disabled", "disabled")
            for (let i = 0; i < event.target.files.length; i++) {
                const file = event.target.files[i];
                try {
                    if (file.type !== "text/csv" && file.type !== "application/vnd.ms-excel") throw new Error(`invalid file type ${file.type}`)
                    const rawFile = (await readFile(file)).trim()
                    const header = rawFile.split('\n')[0];
                    const [content] = csvToArray(rawFile)
                    const fileValidationResult = FormatValidator.isValid(header, content, file.name);
                    if (!fileValidationResult.content) throw new Error(`invalid format ${file.name}`)
                    else if (!fileValidationResult.filename) throw new Error(`invalid file name ${file.name}`)
                    page.isFileValid = true;
                    page.data = rawFile;
                    page.fileName = file.name;
                } catch (error) {
                    if (error.toString().includes("invalid")) alert(error);
                    page.isFileValid = false;
                    isFailed = true
                    console.error(error);
                }
            }
            if (isFailed) {
                page.clearData()
                const fileInput = document.querySelector('input[type="file"]');
                fileInput.value = null;
                $("#submit").attr("disabled", "disabled")
            }
            else {
                if (event.target.files.length > 0)
                    $("#submit").removeAttr("disabled")
                else $("#submit").attr("disabled", "disabled")
            }
        });
    }

    function getRoutes() {
        fetch("/api/v1/get_menu_tree.php?Key=text&type=json")
            .then((res) => res.json())
            .then(res => {
                createTree(parseMenuData(res));
            })
            .catch(() => {
                alert("Internal Server Error")
            })
    }

    function parseMenuData(data) {
        return data.map(item => {
            return {
                id: item.id.toString(),
                text: `${item.unique_id.toString()}-${item.name.toString()}`,
                parent: item.parent.toString() === "0" ? "#" : item.parent.toString()
            }
        })
    }

    function createTree(data) {
        $('#parent')
            .on('changed.jstree', function (e, data) {
                selectedRouteId = data.selected[0];
            })
            .jstree({
                "plugins": [],
                'core': {
                    'data': data,
                    "multiple": false,
                }
            })
    }

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

    function showProgress() {
        page.finished = 0;
        $("#progress").attr("max", page.requestCount)
        $("#progress").attr("min", 0)
        $("#progress").attr("value", page.finished)
        $("#progress-wrapper").css("visibility", "visible")
    }
    function hideProgress() {
        $("#progress-wrapper").css("visibility", "collapse")
    }

    async function importRequest() {
        showProgress();
        let allRequestCount = page.data.length;
        let failedFiles = [];
        for (let index = 0; index < page.data.length; index++) {
            const csvData = page.data[index];
            const [data, header] = csvToArray(csvData);
            if (page.dataType(data) === "Prediction")
                await importPrediction(data, index)
                    .then(res => {
                         //console.log(res)
                        if (!res) {
                            failedFiles.push(page.fileName[index] + " Err: Prediction no res");
                        }
                    })
                    .catch((error) => {
                        console.log(error);
                        failedFiles.push(page.fileName[index] + " Err: Prediction has err");
                    })
                    .finally(() => {
                        page.finished++
                    })
            else if (page.dataType(data) === "Average") importAverage(data, index)
                .then(res => {
                    if (!res) {
                        failedFiles.push(page.fileName[index] + " Err: Average no res");
                    }
                })
                .catch((error) => {
                    console.log(error);
                    failedFiles.push(page.fileName[index] + " Err: Prediction has err");
                })
                .finally(() => {
                    page.finished++
                });
            else
                await importActualValue(data, index)
                    .then(res => {
                        if (!res) {
                            failedFiles.push(page.fileName[index] + " Err: Actual no res");
                        }
                    })
                    .catch((error) => {
                        console.log(error);
                        failedFiles.push(page.fileName[index] + " Err: Prediction has err");
                    })
                    .finally(() => {
                        page.finished++
                    })
        }
        showUploadResult(failedFiles)

    }


    async function showUploadResult(failedFiles) {
        hideProgress()
        page.clearData()
        const fileInput = document.querySelector('input[type="file"]');
        fileInput.value = null;
        let failedCount = failedFiles.length;
        if (failedCount === 0) alert("success")
        else {
			console.log(failedFiles);
            alert(`failed to send ${failedCount} files`)
            const csvTable = document.getElementById('csvTable');
            csvTable.innerHTML = "";
            const csvData = ["Can't Send This Files"].concat(failedFiles).join("\n");
            csvTable.appendChild(csvToTable(csvData, false));
        }
    }

    async function importPrediction(fileContents, index) {
        const formData = new FormData();
        formData.append("action", "import")
        for (var i = 0; i < fileContents.length; i++) {
            formData.append("data[]", page.autoParser(fileContents[i], index));
        }
        return await sendRequest(formData)
    }

    async function importActualValue(fileContents, index) {
        const data = page.autoParser(fileContents, index);
        const formData = new FormData();
        formData.append("action", "create")
        formData.append("data", data);
        return await sendRequest(formData)
    }
    async function importAverage(fileContents, index) {
        const formData = new FormData();
        formData.append("action", "edit_average")
        for (var i = 0; i < fileContents.length; i++) {
            formData.append("data[]", page.AverageParser(fileContents[i], index));
        }
        return await sendAverageRequest(formData)
    }

    function sendAverageRequest(formData) {
        return new Promise((resolve, reject) => {
            $("#submit").attr("disabled", "disabled")
            fetch("/api/v1/routes.php", {
                body: formData,
                method: "POST"
            })
                .then(async (res) => {
                    const result = await res.json()
                    if (result.code === 200) {
                        $("input[type='file']").val("")
                        resolve(true)
                    } else {
                        console.log(result);
                        resolve(false)
                    }
                })
                .catch((err) => {
                    console.log(err);
                    resolve(false)
                })
                .finally(() => $("#submit").removeAttr("disabled"))
        })
    }
    function sendRequest(formData) {
        //return;
        return new Promise((resolve, reject) => {
            $("#submit").attr("disabled", "disabled")
            fetch("/api/v1/routes_data.php", {
                body: formData,
                method: "POST"
            })
                .then(async (res) => {
                    const result = await res.json()
                    if (result.code === 200) {
                        $("input[type='file']").val("")
                        resolve(true)
                    } else {
                        resolve(false)
                    }
                })
                .catch((err) => {
                    console.log(err);
                    resolve(false)
                })
                .finally(() => $("#submit").removeAttr("disabled"))
        })
    }




    function downloadCSV(data = "year, lable, value, unique_id, is_real", name = "data") {
        const downloadLink = document.createElement('a');
        downloadLink.setAttribute('href', 'data:text/csv;charset=utf-8,' + encodeURIComponent(data));
        downloadLink.setAttribute('download', `${name}.csv`);
        document.body.appendChild(downloadLink);
        downloadLink.click();
    }

    /**@param {string} csv  */
    function csvToArray(csv, upperCase = true) {
        const csvContent = Papa.parse(csv.replace(new RegExp(/\r/g), "").replace(new RegExp(/\t/g), ""), { header: true });
        const data = csvContent.data
        const header = upperCase ? csvContent.meta.fields.map(i => i.toUpperCase().replace(/\s/g, "")) : csvContent.meta.fields;

        return [data, header];
    }
    let selectedRouteId = undefined;
    const page = new ImportPageProperties();


    $(() => {
        bootstrap()
    });
</script>

<?php require_once THEMES . 'templates/footer.php'; ?>