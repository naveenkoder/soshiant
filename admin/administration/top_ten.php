<?php


require_once __DIR__ . '/../maincore.php';
require_once __DIR__ . '/../models/users.php';
require_once THEMES . 'templates/admin_header.php';
pageAccess('TT');
#region jstree
// Add jstree to head
add_to_head('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css" />');
add_to_head('<script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/jstree.min.js"></script>');
add_to_head('<script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.4.1/papaparse.min.js"></script>');
#endregion jstree
#region datatree
add_to_head('<script src="/includes/datatree/data-tree.js"></script>');
add_to_head('<link rel="stylesheet" href="/includes/datatree/data-tree.css" />');
#endregion datatree
?>
<div class="container" style="padding: 30px;">
  <div class="row">
    <div class="col-md-4">
      <div class="form-group">
        <label for="file">File input</label>
        <input type="file" name="file" accept="text/csv" id="file" class="form-control">
        <p id="error-label" class="text-danger text-hide">Invalid File</p>
      </div>
      <div class="form-group">
        <Button class="btn btn-success" id="submit" disabled>Import</Button>
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
  class ImportPageProperties {
    #isFileValid = false;
    #data = "";



    get isFileValid() {
      return this.#isFileValid;
    }
    set isFileValid(value) {
      this.#isFileValid = Boolean(value);
      if (this.#isFileValid) {
        $("#error-label").addClass("text-hide")

      } else {
        $("#error-label").removeClass("text-hide")
      }

    }
    get data() {
      return this.#data;
    }
    set data(value) {
      this.#data = value;
      if (!this.#isFileValid || this.data === "") {
        $("#submit").attr("disabled", "disabled")
      } else {
        $("#submit").removeAttr("disabled")
      }
    }
  }


  class FormatValidator {

    /**
     * @param {string} header
     */
    static isTopTenContentValid(header) {
      const items = ["UNIQUE_ID", "VALUE1", "VALUE2", "VALUE3", "TITLE1", "TITLE2", "TITLE3", "CL1", "CL2", "CL3", "AVG1", "AVG2", "AVG3"];
      return this.validator(header, items)
    }

    static isValid(header, filename) {
      const isActualValueContentValid = this.isTopTenContentValid(header)
      if (isActualValueContentValid) {
        return {
          content: isActualValueContentValid,
          filename: isActualValueFileNameValid
        }
      }
    }

    /**
     * @param {string} header
     * @param {string[]} items
     * 
     */
    static validator(header, items) {
      return header.split(",").map(i => i.replace(/\s/g, "").toUpperCase()).every(i => items.includes(i))
    }
  }


  let selectedRoutesId = [];
  let routes = [];
  const page = new ImportPageProperties();
  page.data = "";
  $(() => {
    bootstrap()
  });

  function bootstrap() {
    getRoutes();
    const fileInput = document.querySelector('input[type="file"]');
    const csvTable = document.getElementById('csvTable');
    document.getElementById("submit").onclick = () => {
      importRequest();
      fileInput.value = "";
      csvTable.innerHTML = "";
      page.data = "";
    }
    fileInput.addEventListener('change', async (event) => {
      csvTable.innerHTML = "";
      const file = event.target.files[0];
      page.isFileValid = true;
      page.data = "";
      try {
        if (!file) return;
        if (file.type !== "text/csv") throw new Error("invalid file type")
        const rawFile = (await readFile(file)).trim()
        const [fileContents, header] = csvToArray(rawFile);
        const fileValidator = ["id", "title", "value"].map(i => i.toLocaleUpperCase())
        if (!header.map(i => i.toUpperCase()).every(i => fileValidator.includes(i))) {
          alert("Invalid file content")
          throw new Error("Invalid file content")
        }
        csvTable.appendChild(csvToTable(rawFile));
        page.isFileValid = true;
        page.data = fileContents;
      } catch (error) {
        page.isFileValid = false;
        page.data = "";
        console.error(error);
      }
    });
  }

  function getRoutes() {
    fetch("/api/v1/get_menu_tree.php?key=text&type=json")
      .then((res) => res.json())
      .then((res) => {
        routes = res;
        // createTree(parseMenuData(res));
      })
      .catch(() => {
        alert("Internal Server Error");
      });
  }

  /**
   * @typedef {Object[]} TopTenData
   * @property {number} ID - The unique identifier of the data entry.
   * @property {string} TITLE - The title of the data entry.
   * @property {number} VALUE - The value of the data entry.
   */

  /**@param {TopTenData} data */
  function parseData(data) {
    /**@type {{}} */
    console.log(data);
    const GroupById = data.reduce((value, item, index, array) => {
      if (value[item.ID.toString()] === undefined) value[item.ID.toString()] = {};
      value[item.ID.toString()][item.TITLE] = Number(item.VALUE);
      return value;
    }, {})
    console.log(GroupById)
    const result = [];
    for (const [unique_id, top_ten] of Object.entries(GroupById)) {
      result.push({
        unique_id,
        top_ten: JSON.stringify(top_ten)
      })
    }
    console.log(result)
    return result;
  }

  function importRequest() {
    const formData = new FormData();
    formData.append("action", "import")
    const data = parseData(page.data)
    for (var i = 0; i < data.length; i++) {
      const item = JSON.stringify(data[i]);
      formData.append("data[]", item);
    }
    $("#submit").attr("disabled", "disabled")
    fetch("/api/v1/top_ten.php", {
      body: formData,
      method: "POST"
    })
      .then(async (res) => {
        const text = await res.json();
        const message = text.message;
        if (res.status === 200) {
          alert("Success")
        } else {
          alert(message)
        }
      })
      .catch((error) => {
        alert("Internal Server Error")
      })
      .finally(() => $("#submit").removeAttr("disabled"))
  }

  function downloadCSV(data) {
    const downloadLink = document.createElement('a');
    downloadLink.setAttribute('href', 'data:text/csv;charset=utf-8,' + encodeURIComponent(data));
    downloadLink.setAttribute('download', 'data.csv');
    document.body.appendChild(downloadLink);
    downloadLink.click();
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


  function parseMenuData(data) {
    return data.map((item) => {
      return {
        id: item.id.toString(),
        text: `${item.unique_id.toString()}-${item.name.toString()}`,
        parent: item.parent.toString() === "0" ? "#" : item.parent.toString(),
      };
    });
  }

  function csvToTable(csvText) {
    const [data, headers] = csvToArray(csvText);

    console.log({ data });
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
  /**@param {string} csv  */
  function csvToArray(csv) {
    const txt = csv.split("\n");
    txt[0] = txt[0].toUpperCase().replace(/\s/g, "")
    const csvContent = Papa.parse(txt.join("\n"), { header: true });
    const data = csvContent.data
    const header = csvContent.meta.fields.map(i => i.toUpperCase());
    return [data, header];
  }


  function createTree(data) {
    $('#frmt')
      .on('changed.jstree', function (e, data) {
        selectedRoutesId = data.selected;
      })
      .jstree({
        "plugins": [],
        'core': {
          multiple: false,
          'data': data
        }
      })
  }
</script>

<?php require_once THEMES . 'templates/footer.php'; ?>