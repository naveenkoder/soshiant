<?php
require_once __DIR__ . '/../maincore.php';
require_once THEMES . 'templates/admin_header.php';
pageAccess('UDOC');
?>

<style>
    .container {
        padding: 30px 20px;
    }
</style>

<div class="container">
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label>Select Chart<sup>*</sup>:</label>
                <select class="form-control" id="chart-type">
                    <option value="user-panel-chart-accuracy">Evaluation Chart</option>
                    <option value="user-panel-chart-history">3 Steps Aead</option>
                    <option value="user-panel-chart-changes">Percentage Changes</option>
                    <option value="user-panel-chart-gauge">Gauge</option>
                    <option value="user-panel-chart-topten">Most Effective Drivers</option>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Chart Name<sup>*</sup>:</label>
                <input type="text" class="form-control" placeholder="Chart Name" id="title" />
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Chart Description<sup>*</sup>:</label>
                <br>
                <textarea placeholder="Chart Description" id="description" cols="50" rows="5"></textarea>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <Button class="btn btn-success" id="submit">Submit</Button>
            </div>
        </div>

    </div>
</div>

<script>

    let chartValues = []; // Array to store the chart values

    $(() => bootstrap());

    // Function: encodeBase64
    // Description: Encodes the given text into Base64 format.
    // Parameters:
    // - text: The text to be encoded.
    // Returns: The encoded Base64 string.
    function encodeBase64(text) {
        return btoa(unescape(encodeURIComponent(text)));
    }

    // Function: decodeBase64
    // Description: Decodes the given Base64 string into text.
    // Parameters:
    // - base64: The Base64 string to be decoded.
    // Returns: The decoded text.
    function decodeBase64(base64) {
        return decodeURIComponent(escape(atob(base64)));
    }

    // Function: bootstrap
    // Description: Initializes the page by setting up event listeners and fetching settings.
    function bootstrap() {
        console.log("Bootstrap");
        getSettingsRequest();
        $("#submit").on("click", () => {
            set();
        });

        $("#chart-type").on("change", (e) => {
            console.log(e);
            setCurrentValues(e.target.value);
        });
    }

    // Function: getSettingsRequest
    // Description: Sends a request to get the chart settings from the server.
    function getSettingsRequest() {
        fetch("/api/v1/settings.php?action=get_charts_settings")
            .then(async (res) => {
                const response = await res.json();
                chartValues = response;
                console.log(response);
                setCurrentValues($("#chart-type").val());
            })
            .catch((e) => {
                console.log(e);
                alert("Internal Server Error");
            });
    }

    // Function: setCurrentValues
    // Description: Sets the current values for the selected chart type.
    // Parameters:
    // - chartType: The selected chart type.
    function setCurrentValues(chartType) {
        const titleTag = $("#title");
        const descriptionTag = $("#description");
        let data = chartValues.find((i) => i.chartName === chartType)?.value || "{}";
        try {
            data = JSON.parse(data);
        } catch (error) {
            console.log(error);
            console.log(data);
        }
        console.log({
            data,
            chartType,
            chartValues,
        });
        try {
            const title = data?.title || "";
            const description = data?.description || "";
            titleTag.val(decodeBase64(title));
            descriptionTag.val(decodeBase64(description)?.replace(/\<br\>/gi, "\n") || "");
        } catch (error) {
            alert("Incorrect format. Title and Description must be Base64 encoded.");
        }
    }

    // Function: set
    // Description: Sends a request to set the chart settings on the server.
    function set() {
        const formData = new FormData();
        const chartType = $("#chart-type").val();
        const value = JSON.stringify({
            title: encodeBase64($("#title").val()),
            description: encodeBase64($("#description").val()).trim().replace(/[\r\t]/gi, " ").replace(/\n/gi, "<br>"),
        });
        formData.append("action", "set_charts_settings");
        formData.append("chartType", chartType);
        formData.append("value", value);
        console.log({
            chartType,
            value,
        });
        console.log(formData.keys());
        fetch("/api/v1/settings.php", {
            body: formData,
            method: "POST",
        })
            .then(async (res) => {
                const responseText = await res.json();
                console.log({ responseText, condition: responseText.code !== 200 });
                if (responseText.code !== 200) throw new Error(res);
                alert("Success");
                window.location.reload();
            })
            .catch((e) => {
                console.log(e);
                alert("Internal Server Error");
            });
    }


</script>

<?php require_once THEMES . 'templates/footer.php'; ?>