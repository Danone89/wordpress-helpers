var DataSource = function(settings) {
  (this.url = settings.url), (this.nonce = settings.url);
};

function DatatableAjaxClient(params) {
  this.params = [];
}
DatatableAjaxClient.prototype.WPRestAPI = function(data, callback, settings) {
  var $params = this.params;
  //  wp.api.loadPromise.done(function() {
  //use wp api client
  jQuery.ajax({
    url: $params.table.href,
    data: data,
    success: function(data, status, xhr) {
      var pages = xhr.getResponseHeader("X-WP-TOTALPAGES");
      var total = xhr.getResponseHeader("X-WP-TOTAL");
      var draw = xhr.getResponseHeader("X-DT-DRAW");

      callback(data);
    },
    beforeSend: function(request) {
      request.setRequestHeader("X-WP-NONCE", $params.nonce);
    },
    fail: function(xhr, status, error) {
      alert("Ładowanie danych nie powiodło się: " + error);
    }
  });
  // });
};
DatatableAjaxClient.prototype.jsonpClient = function(data, callback, settings) {
  var $table = jQuery(settings.oInstance.selector);
  jQuery.ajax({
    url: $table.attr("data-href") + "?" + $table.attr("data-query"),
    jsonp: "callback",
    dataType: "jsonp",
    success: function(result) {
      callback({ data: result });
    },

    fail: function(xhr, status, error) {
      alert("Ładowanie danych nie powiodło się: " + error);
    }
  });
};

function initTable(config, ajaxFunction = "") {
  let params = config;

  if (params.table.ajax) {
    var Client = new DatatableAjaxClient();
    Client.params = params;
    if (params.table.ajax.dataType == "jsonp") {
      params.table.ajax = function(data, callback, settings) {
        Client.jsonpClient(data, callback, settings);
      };
    } else {
      if (!params.table.serverSide) {
        if (!params.table.ajax.dataSrc) params.table.ajax.dataSrc = "";
      }
      params.table.ajax.beforeSend = function(request, settings) {
        request.setRequestHeader("X-WP-NONCE", params.nonce);
        if (!settings.serverSide) {
          settings.url =
            settings.url + "&" + jQuery(params.selector).attr("data-query");
        }
      };
    }
  }
  params.table.language = window[params.table.language];
  jQuery(document).trigger("beforeTableInit", params); // [params.selector.slice(1)]
  var table = jQuery(params.selector).DataTable(params.table);

  return table;
}
