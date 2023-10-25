<?php
require_once __DIR__ . '/../maincore.php';
require_once THEMES . 'templates/admin_header.php';
pageAccess('EX');
#region jstree
// Add jstree to head
add_to_head('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css" />');
add_to_head('<script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/jstree.min.js"></script>');
add_to_head('<script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.3.0/papaparse.min.js"></script>');
#endregion jstree
?>

<style>
  .row {
    margin: 25px !important;
    padding: 10px !important;
  }

  .spinner {
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

  .onDownload {
    visibility: collapse;
  }

  .container {
    width: 100%;
  }

  input {
    width: 70%
  }
</style>

<div class="container">
  <div class="row">
    <!-- <div class="col-md-8">
      <div>
        <label>Search:</label>
        <input type="text" class="form-control" id="search">
      </div>
      <div id="frmt"></div>
    </div> -->
    <div class="col-md-4">
      <!-- <div class="radio">
        <label><input type="radio" value="Predictions" name="export_type" checked>Predictions</label>
      </div>
      <div class="radio">
        <label><input type="radio" value="Evaluations" name="export_type">Evaluations</label>
      </div> -->
      <div id="buttons-area">
        <!-- <div id="loading">
          <span>Loading...</span>
          <div class="spinner" id="spinner"></div>
        </div> -->
        <br>
        <div>
          <label for="date">From<sup style="color: red;">*</sup>:</label><br>
          <input type="date" id="from" name="date">
        </div>
        <br>
        <div>
          <label for="date">To<sup style="color: red;">*</sup>:</label><br>
          <input type="date" id="to" name="date">
        </div>
        <br>
        <div>
          <label>Accuracy Report:</label><br>
          <button id="download_data" class="btn btn-primary btn-block">Download</button>
        </div>
        <br>
        <div>
          <label>Accuracy Analysis:</label><br>
          <button id="download_accuracy" class="btn btn-primary btn-block">Download</button>
        </div>
      </div>
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
  $(() => {
    bootstrap()
  });

  async function bootstrap() {
    await getRoutes()
    $("#download_data").on("click", () => {
      exportRequest("data")
    })
    $("#download_accuracy").on("click", () => {
      exportRequest("accuracy")
    })
  }





  function saveAsCSV(data = {}, name = 'Accuracy Report.csv') {
    const parsedData = convertToCSV(data)
    const downloadLink = document.createElement('a');
    console.log(parsedData);
    downloadLink.setAttribute('href', 'data:text/csv;charset=utf-8,' + encodeURIComponent(parsedData));
    downloadLink.setAttribute('download', name);
    document.body.appendChild(downloadLink);
    downloadLink.click();
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

  function createButtons() {
    const periods = [...new Set(routes.map(i => i.period))].filter(i => i !== "")
    periods.forEach(period => {
      const button = document.createElement("button");
      button.setAttribute("class", "btn btn-primary btn-block w-80");
      button.innerText = period;
      button.onclick = () => {
        const targetRoutes = routes.filter(i => i.period === period)
        const isAllSelected = targetRoutes.every(i => selectedRoutesId.map(String).includes(String(i.id)));
        if (isAllSelected) {
          $('#frmt')
            .jstree("deselect_node", routes.filter(i => i.period === period).map(i => i.id));
        }
        else {
          $('#frmt')
            .jstree("select_node", routes.filter(i => i.period === period).map(i => i.id));
        }
      }
      document.getElementById("buttons-area").appendChild(button);
    })

  }


  // function downloadRoutes(data, accuracy) {
  //   // return console.log(data);
  //   const csvData = convertToCSV(data);
  //   const csvAccuracy = convertToCSV(accuracy);
  //   saveAsCSV(csvData, `Data_${new Date().toISOString()}.csv`)
  //   saveAsCSV(csvAccuracy, `Accuracy_${new Date().toISOString()}.csv`)
  // }

  function calcError(actual, predicted) {
    const actualValue = Number(actual);
    const predictedValue = Number(predicted);
    if (
      Number.isNaN(actualValue) ||
      Number.isNaN(predictedValue)
    ) return "N/A"
    if (predictedValue === actualValue) {
      return 100;
    } else {
      let error = Math.abs(predictedValue - actualValue);
      let accuracy = 1 - (error / actualValue);
      return accuracy * 100;
    }
  }




  function convertToDate(
    year,
    month,
    day,
  ) {
    return new Date(Number(year), Number(month) - 1, Number(day));
  }
  function getEvaluations(evaluationsData) {
    const evaluations = Object.values(evaluationsData);
    return evaluations.map((currentData) => {
      /**
       * @type {evaluationsValue[]}
       */
      const values = JSON.parse(currentData.value);
      const evaluationsValue = values.map((value) => {
        const time = value.time.replace(/\D/g, "");
        return {
          actualValue: Number(value.Actual_value),
          time,
          title: value.Title,
          date: convertStringToDate(time),
        };
      });

      return {
        evaluations: evaluationsValue,
        unique_id: currentData.unique_id,
      };
    });
  }

  function getPredictionsValue(predictionsData) {
    return predictionsData.reduce((result, currentData) => {
      const value = JSON.parse(currentData.value);
      const more_info = JSON.parse(currentData.more_info);
      const sortedPeriod = Object.keys(value).sort((a, b) => a.localeCompare(b));
      const key = currentData.unique_id;
      sortedPeriod.forEach((period, index) => {
        const dataIndex = index + 1;
        result[key] = result[key] || {};
        result[key][period] = result[key][period] || [];
        result[key][period] = result[key][period] || [];
        result[key][period].push({
          value: value[period],
          time: currentData.create_time * 1000,
          id: currentData.id,
          uploadTime: currentData.create_time,
          index: dataIndex,
          confidenceLevel: more_info["CL" + dataIndex]
        })
      });
      return result;
    }, {});
  }




  async function exportRequest(dataType = ("data" || "accuracy")) {
    console.log("Exporting...");
    const formDataPredictions = new FormData();
    formDataPredictions.append("action", `get_predicted_history`)
    const formDataEvaluations = new FormData();
    formDataEvaluations.append("action", `get_evaluation`)
    const predictions = await fetch("/api/v1/routes_data.php", {
      body: formDataPredictions,
      method: "POST"
    })
      .then(async (res) => {
        let data = await res.json();
        if (!Array.isArray(data) || data?.length === 0) {
          throw new Error("Data unavailable")
        }
        return data;
      })
      .catch((error) => {
        console.error(error);
        alert("Can't Get Predictions")
      })

    const evaluations = await fetch("/api/v1/routes_data.php", {
      body: formDataEvaluations,
      method: "POST"
    })
      .then(async (res) => {
        let data = await res.json();
        return data || {};
      })
      .catch((error) => {
        console.error(error);
        alert("Can't Get Evaluations")
      })
    if (predictions && evaluations) {
      const parsedData = parseData(predictions, evaluations)
      if (dataType === "data") {
        saveAsCSV(getExportData(parsedData));
      }
      else {
        saveAsCSV(getExportAccuracy(parsedData), "Accuracy Analysis.csv");
      }
    }
  }


  function getExportData(data) {
     const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    return data.filter(item => {
      const dateRange = getDateRange()
      const dataDate = new Date(item.date);
      const isGreatThanRange = dateRange.from <= dataDate;
      const isLessThanRange = dateRange.to >= dataDate;
      return isGreatThanRange && isLessThanRange
    }).sort((a, b) => Number(a.uniqueId) - Number(b.uniqueId)).map(item => {
      return {
        CID: item.uniqueId,
        uploadTime: (new Date(item.uploadTime)).toLocaleDateString("en-US", options),
        Description: item.name,
        Period: item.period,
        TargetTime: (new Date(item.date)).toLocaleDateString("en-US", options),
        Actual: item.actualValue,
        "Prediction - 1": item.firstPrediction,
        "Prediction - 2": item.secondPrediction,
        "Prediction - 3": item.thirdPrediction,
        "Accuracy - 1": calcError(item.actualValue, item.firstPrediction),
        "Accuracy - 2": calcError(item.actualValue, item.secondPrediction),
        "Accuracy - 3": calcError(item.actualValue, item.thirdPrediction),
        "Confidence Level - 1": item.firstConfidenceLevel,
        "Confidence Level - 2": item.secondConfidenceLevel,
        "Confidence Level - 3": item.thirdConfidenceLevel,
        
      }
    }).sort((a, b) => new Date(b.uploadTime) - new Date(a.uploadTime))
  }

  function getExportAccuracy(input) {
    const data = getExportData(input);
    const accuracy = data.reduce((result, current) => {
      result[current.CID] = result[current.CID] || {};
      const Accuracy_1 = result[current.CID]["Accuracy - 1"];
      const Accuracy_2 = result[current.CID]["Accuracy - 2"];
      const Accuracy_3 = result[current.CID]["Accuracy - 3"];
      result[current.CID]["Accuracy - 1"] = Accuracy_1 || "N/A";
      result[current.CID]["Accuracy - 2"] = Accuracy_2 || "N/A";
      result[current.CID]["Accuracy - 3"] = Accuracy_3 || "N/A";
      result[current.CID]["sum1"] = result[current.CID]["sum1"] || 0;
      result[current.CID]["count1"] = result[current.CID]["count1"] || 0;
      result[current.CID]["sum2"] = result[current.CID]["sum2"] || 0;
      result[current.CID]["count2"] = result[current.CID]["count2"] || 0;
      result[current.CID]["sum3"] = result[current.CID]["sum3"] || 0;
      result[current.CID]["count3"] = result[current.CID]["count3"] || 0;
      if (!Number.isNaN(Number(current["Accuracy - 1"]))) {
        if (result[current.CID]["Accuracy - 1"] > Number(current["Accuracy - 1"]) || Number.isNaN(Number(result[current.CID]["Accuracy - 1"])))
          result[current.CID]["Accuracy - 1"] = Number(current["Accuracy - 1"]) ? Number(current["Accuracy - 1"]) : result[current.CID]["Accuracy - 1"]
        result[current.CID]["sum1"] += (Number(current["Accuracy - 1"]) || 0);
        result[current.CID]["count1"] += (Number.isNaN(Number(current["Accuracy - 1"])) ? 0 : 1);
      }
      if (!Number.isNaN(Number(current["Accuracy - 2"]))) {
        if (result[current.CID]["Accuracy - 2"] > Number(current["Accuracy - 2"]) || Number.isNaN(Number(result[current.CID]["Accuracy - 2"])))
          result[current.CID]["Accuracy - 2"] = Number(current["Accuracy - 2"]) ? Number(current["Accuracy - 2"]) : result[current.CID]["Accuracy - 2"]
        result[current.CID]["sum2"] += (Number(current["Accuracy - 2"]) || 0);
        result[current.CID]["count2"] += (Number.isNaN(Number(current["Accuracy - 2"])) ? 0 : 1);
      }
      if (!Number.isNaN(Number(current["Accuracy - 3"]))) {
        if (result[current.CID]["Accuracy - 3"] > Number(current["Accuracy - 3"]) || Number.isNaN(Number(result[current.CID]["Accuracy - 3"])))
          result[current.CID]["Accuracy - 3"] = Number(current["Accuracy - 3"]) ? Number(current["Accuracy - 3"]) : result[current.CID]["Accuracy - 3"]
        result[current.CID]["sum3"] += (Number(current["Accuracy - 3"]) || 0);
        result[current.CID]["count3"] += (Number.isNaN(Number(current["Accuracy - 3"])) ? 0 : 1);
      }
      return result;
    }, {})
    return Object.keys(accuracy).map(CID => {
      const route = routes.find(i => {
        return String(i.unique_id) == String(CID);
      });
      const parent = routes.find(i => {
        return i.id == route?.parent;
      });

      return {
        CID,
        Description: parent?.name,
        Period: route?.period,
        "Minimum Accuracy Prediction - 1": accuracy[CID]["Accuracy - 1"],
        "Average Accuracy Prediction - 1": accuracy[CID]["sum1"] / accuracy[CID]["count1"],
        "Minimum Accuracy Prediction - 2": accuracy[CID]["Accuracy - 2"],
        "Average Accuracy Prediction - 2": accuracy[CID]["sum2"] / accuracy[CID]["count2"],
        "Minimum Accuracy Prediction - 3": accuracy[CID]["Accuracy - 3"],
        "Average Accuracy Prediction - 3": accuracy[CID]["sum3"] / accuracy[CID]["count3"]
      }
    })
  }


  function convertStringToDate(dateString) {
    let year = dateString.substring(0, 4);
    let month = dateString.substring(4, 6) - 1; // Note: month is zero-indexed
    let day = dateString.substring(6, 8);
    return new Date(year, month, day);
  }


  function parseData(
    predictionsData,
    evaluationsData
  ) {
    const parsedPredictions = getPredictionsValue(predictionsData);
    const parsedEvaluations = getEvaluations(evaluationsData);
    return parsedEvaluations
      .map((evaluation) => {
        return evaluation.evaluations.map((evaluationsValue) => {
          const predictionData = parsedPredictions[evaluation.unique_id] || {};
          const prediction = predictionData[evaluationsValue.title]?.sort((a, b) => b.time - a.time);
          const route = routes.find((i) => String(i.unique_id) === String(evaluation.unique_id));
          const parent = routes.find((i) => String(i.id) == String(route.parent));
          const firstPredictions = prediction?.filter((i) => i.index === 1);
          const result = [];
          firstPredictions?.forEach((firstPrediction, index) => {
            const secondPrediction = prediction?.filter((i) => i.index === 2)[index];
            const thirdPrediction = prediction?.filter((i) => i.index === 3)[index];
            result.push({
              ...evaluationsValue,
              uniqueId: evaluation.unique_id,
              name: parent.name,
              period: route.period,
              // uploadTime: new Date(prediction?.find((i) => i.index === 1)?.time || 0),
              uploadTime: new Date(firstPrediction.uploadTime * 1000),
              firstPrediction: firstPrediction?.value || "N/A",
              secondPrediction: secondPrediction?.value || "N/A",
              thirdPrediction: thirdPrediction?.value || "N/A",
              firstConfidenceLevel: firstPrediction?.confidenceLevel || "N/A",
              secondConfidenceLevel: secondPrediction?.confidenceLevel || "N/A",
              thirdConfidenceLevel: thirdPrediction?.confidenceLevel || "N/A",
            })
          })
          return result;
        });
      })
      .flat(2)
      .filter(
        (i) =>
          i.firstPrediction !== "N/A" ||
          i.secondPrediction !== "N/A" ||
          i.thirdPrediction !== "N/A"
      );
  }



  let retryGetRoutes = 0;
  async function getRoutes() {
    await fetch("/api/v1/get_menu_tree.php?key=text&type=json")
      .then((res) => res.json())
      .then((res) => {
        routes = res;
        console.log(res)
        createTree(parseMenuData(res));
      })
      .catch(() => {
        if (retryGetRoutes < 3) {
          retryGetRoutes++;
          getRoutes()
        }
        else
          alert("Internal Server Error");
      });
  }

  function parseMenuData(data) {
    return data.map((item) => {
      return {
        id: item.id?.toString(),
        text: `${item.unique_id?.toString()}-${item.name?.toString()}`,
        parent: item.parent?.toString() === "0" ? "#" : item.parent?.toString(),
      };
    });
  }

  function getDateRange() {
    const from = $("#from").val().split("-");
    const to = $("#to").val().split("-");
    const fromYear = from[0];
    const fromMonth = from[1];
    const fromDay = from[2];
    const toYear = to[0];
    const toMonth = to[1];
    const toDay = to[2];
    return {
      from: convertToDate(
        fromYear,
        fromMonth,
        fromDay
      ),
      to: convertToDate(
        toYear,
        toMonth,
        toDay,
      ),
    }
  }



  function createTree(data) {
    $('#frmt')
      .on('changed.jstree', function (e, data) {
        selectedRoutesId = data.selected;
      })
      .jstree({
        "plugins": ["wholerow", "checkbox", "search"],
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