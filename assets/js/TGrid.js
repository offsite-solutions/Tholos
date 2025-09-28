function TGrid_getRandomColor() { // f
  var letters = '0123456789ABCDEF'.split('');
  var color = '#';
  for (var i = 0; i < 6; i++) {
    color += letters[Math.floor(Math.random() * 16)];
  }
  return color;
}

window.chartColors = {
  red: 'rgb(223,81,56)',
  orange: 'rgb(255, 159, 64)',
  yellow: 'rgb(255, 205, 86)',
  green: 'rgb(140,196,116)',
  blue: 'rgb(93,178,255)',
  purple: 'rgb(153, 102, 255)',
  grey: 'rgb(201, 203, 207)'
};

function TGrid_explode(delimiter, string, limit) {                                     // php like explode
  //   discuss at: http://phpjs.org/functions/explode/
  //   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //   example 1: explode(' ', 'Kevin van Zonneveld');
  //   returns 1: {0: 'Kevin', 1: 'van', 2: 'Zonneveld'}

  if (arguments.length < 2 || typeof delimiter === 'undefined' || typeof string === 'undefined') return null;
  if (delimiter === '' || delimiter === false || delimiter === null) return false;
  if (typeof delimiter === 'function' || typeof delimiter === 'object' || typeof string === 'function' || typeof string ===
    'object') {
    return {
      0: ''
    };
  }
  if (delimiter === true) delimiter = '1';

  // Here we go...
  delimiter += '';
  string += '';

  var s = string.split(delimiter);

  if (typeof limit === 'undefined') return s;

  // Support for limit
  if (limit === 0) limit = 1;

  // Positive limit
  if (limit > 0) {
    if (limit >= s.length) return s;
    return s.slice(0, limit - 1)
      .concat([s.slice(limit - 1)
        .join(delimiter)
      ]);
  }

  // Negative limit
  if (-limit >= s.length) return [];

  s.splice(s.length + limit);
  return s;
}

$(function () {                                                                    // oszlopszelesseg beallitasa
  $("table[data-resizable-columns-id]").resizableColumns({
    store: window.store
  });
});

function TGrid_inArray(searchFor_, array_) {                               // PHP in_array fuggveny
  var length = array_.length;
  for (var i = 0; i < length; i++) {
    if (array_[i] == searchFor_) return true;
  }
  return false;
}

function TGrid_parseChartData(formId_, data_) {
  var TholosChartColors = [
    window.chartColors.green,
    window.chartColors.red,
    window.chartColors.blue,
    window.chartColors.grey,
    window.chartColors.purple,
    window.chartColors.orange,
    window.chartColors.yellow];

  var TholosChartColor = Chart.helpers.color;

  var parsedData = {
    datasets: []
  };

  if (data_.length > 1) {
    for (var i = 0; i < data_.length; i++) {
      if (i === 0) {
        parsedData['labels'] = data_[0];
      } else {
        var newChartData = {
          type: 'line',
          fill: 'false',
          data: data_[i],
          borderColor: TholosChartColors[(i-1) % 7],
          backgroundColor: TholosChartColor(TholosChartColors[(i-1) % 7]).alpha(0.8).rgbString()
        };
        parsedData['datasets'].push(newChartData);
      }
    }
  }
  $('.subscribers-' + formId_).trigger('chartDataRefresh', parsedData);
}

function TGrid_changeViewMode(formId_, viewMode_) {
  $("#helper_" + formId_ + " #TGrid_ViewMode_").val(viewMode_);
  Tholos.eventHandler(formId_, formId_, 'TGrid', 'refresh');
}

function TGrid_submit(sender, target, urldata) {                                           // grid submit
//    if ($("#helper_" + formId_ + " #gridmode").val() == "ajax") {                        // ha ajax, akkor

  if ($("#helper_" + target + " #TGrid_ViewMode_").val() == "GRID") {
    $("#data_" + target).show();
    $("#pagination_" + target).show();
  } else {
    $("#data_" + target).hide();
    $("#pagination_" + target).hide();
  }
  if ($("#helper_" + target + " #TGrid_ViewMode_").val() == "CHART") {
    $("#chart_" + target).show();
  } else {
    $("#chart_" + target).hide();
  }

  if ($("#helper_" + target + " #TGrid_ViewMode_").val() == "GRID") {

    $('.subscribers-' + target).trigger('leavingChartTab');
    $("#GRID_button_" + target).addClass("active");
    $("#CHART_button_" + target).removeClass("active");
  } else {
    $("#CHART_button_" + target).addClass("active");
    $("#GRID_button_" + target).removeClass("active");
  }

  if (typeof urldata == "undefined" || urldata === null) {
    urldata = [];
  }
  if ($("#helper_" + target + ' #TGrid_ViewMode_').val() == "GRID") {
    $("#container_" + target).fadeTo(200, 0.2);                                   // atlatszosag az adatok reszen
    $.ajaxq(
      (Tholos.getData(target).hasOwnProperty('ajaxqueueid') ? Tholos.getData(target)['ajaxqueueid'] : target),
      {
        url: $("#helper_" + target).attr('action'),
        // url: '/syslog/index/gridSyslog/',
        type: 'post',
        dataType: 'json',
        data: $("#helper_" + target).serialize() + '&' + $.param(urldata),
        contentType: "application/x-www-form-urlencoded;charset=UTF-8",
        success: function (data) {
          $("#TGrid_" + target).replaceWith(data.html);                // grid megjelenitese
          $(function () {                                                 // oszlopszelesseg visszaallitasa
            $("table[data-resizable-columns-id]").resizableColumns({
              store: window.store
            });
          });
          $("#loader_" + target).hide();
          $("#container_" + target).fadeTo(0, 1);                         // atlatszosag a normalisra
          $("#helper_" + target + " #filters_text #filter_refresh").hide();
          $("#" + target + "-props").off("masterDataChange");
          $("#" + target + "-props").on("masterDataChange", function (e, edata) {
            if ($("#helper_" + $(this).data().id + " #TGrid_MasterValue_").val() != edata) {
              $("#helper_" + $(this).data().id + " #TGrid_MasterValue_").val(edata);
              $("#helper_" + $(this).data().id + " #TGrid_Value_").val('');
              $("#helper_" + $(this).data().id + " #TGrid_ActivePage_").val(1);
              Tholos.eventHandler($(this).data().id, $(this).data().id, 'TGrid', 'refresh');
            }
          });
          $("#" + target + "-props").off("masterRefresh");
          $("#" + target + "-props").on("masterRefresh", function (e, edata) {
            Tholos.eventHandler($(this).data().id, $(this).data().id, 'TGrid', 'refresh');
            $('.subscribers-' + $(this).data().id).trigger('masterRefresh');
          });
          // refreshing details
          $('.subscribers-' + target).trigger('masterDataChange', [$("#helper_" + target + " #TGrid_Value_").val()]);
          // triggering onAfterRefresh
          $('#' + target).trigger("onAfterRefresh", {});
          // workflow compatibility
          Tholos.action(true, sender, target);
        },
        error: function (response, textStatus, errorThrown) {
          if (response.status === 401) location.href = '/';
          Tholos.action(false, sender, target);
        }
      });
  } else if ($("#helper_" + target + ' #TGrid_ViewMode_').val() == "CHART") {
    $.ajaxq(
      (Tholos.getData(target).hasOwnProperty('ajaxqueueid') ? Tholos.getData(target)['ajaxqueueid'] : target),
      {
        url: $("#helper_" + target).attr('action'),
        // url: '/syslog/index/gridSyslog/',
        type: 'post',
        dataType: 'json',
        data: $("#helper_" + target).serialize() + '&' + $.param(urldata),
        contentType: "application/x-www-form-urlencoded;charset=UTF-8",
        success: function (data) {
          $("#needrefresh_" + target).hide();
          TGrid_parseChartData(target, data);
          Tholos.action(true, sender, target);
        },
        error: function (response, textStatus, errorThrown) {
          if (response.status === 401) location.href = '/';
          Tholos.action(false, sender, target);
        }
      });
  } else $("#helper_" + target).submit();                                         // ha nem ajax, akkor sima submit
}

function TGrid_reloadPreviousState(formId_) {                                           // grid submit
//    if ($("#helper_" + formId_ + " #gridmode").val() == "ajax") {                        // ha ajax, akkor
  if (true) {
    $("#loader_" + formId_).show();
    $("#container_" + formId_).hide();                                    // atlatszosag az adatok reszen
    // console.log($("#helper_" + formId_).data());
    var sd = Tholos.getData(formId_);
    var urlparams = "";
    if (sd.dataparameters) {
      urlparams = Tholos.EncodeQueryData(sd.dataparameters);
    }
    $.ajaxq(
      (Tholos.getData(formId_).hasOwnProperty('ajaxqueueid') ? Tholos.getData(formId_)['ajaxqueueid'] : formId_),
      {
        url: $("#helper_" + formId_).attr('action'),
        // url: '/syslog/index/gridSyslog/',
        type: 'post',
        dataType: 'json',
        data: "TGrid_uuid_=" + $("#helper_" + formId_ + " #TGrid_uuid_").val() + "&TGrid_todo_=reloadState&tholos_renderID=" + $("#helper_" + formId_ + " #tholos_renderID").val() + '&' + urlparams,
        contentType: "application/x-www-form-urlencoded;charset=UTF-8",
        success: function (data) {
          $("#TGrid_" + formId_).replaceWith(data.html);                // grid megjelenitese
          $(function () {                                                 // oszlopszelesseg visszaallitasa
            $("table[data-resizable-columns-id]").resizableColumns({
              store: window.store
            });
          });
          $("#loader_" + formId_).hide();
          $("#container_" + formId_).show();                         // atlatszosag a normalisra
          $("#helper_" + formId_ + " #filters_text #filter_refresh").hide();
          $("#" + formId_ + "-props").off("masterDataChange");
          $("#" + formId_ + "-props").on("masterDataChange", function (e, edata) {
            if ($("#helper_" + $(this).data().id + " #TGrid_MasterValue_").val() != edata) {
              $("#helper_" + $(this).data().id + " #TGrid_MasterValue_").val(edata);
              $("#helper_" + $(this).data().id + " #TGrid_Value_").val('');
              $("#helper_" + $(this).data().id + " #TGrid_ActivePage_").val(1);
              Tholos.eventHandler($(this).data().id, $(this).data().id, 'TGrid', 'refresh');
            }
          });
          $("#" + formId_ + "-props").off("masterRefresh");
          $("#" + formId_ + "-props").on("masterRefresh", function (e, edata) {
            Tholos.eventHandler($(this).data().id, $(this).data().id, 'TGrid', 'refresh');
            $('.subscribers-' + $(this).data().id).trigger('masterRefresh');
          });
          $('.subscribers-' + formId_).trigger('masterDataChange', [$("#helper_" + formId_ + " #TGrid_Value_").val()]);
        },
        error: function (response, textStatus, errorThrown) {
          if (response.status === 401) location.href = '/';
        }
      });
  }
}

function TGrid_updateSelection(formId_, value_, forceRefresh_) {
  var cvalue = $("#helper_" + formId_ + " #TGrid_Value_").val();
  if (value_ != cvalue) { // do not refresh when same line is selected (eg. button click on row)
    $('#' + formId_).find('tr').removeClass('active');
    $('#' + formId_).find('td').removeClass('active');
    if (value_ != undefined && value_.length != 0) $('#' + formId_ + '-' + value_).addClass('active');
    $("#helper_" + formId_ + " #TGrid_Value_").val(value_);
    $('.subscribers-' + formId_).trigger('masterDataChange', [value_]);
    $('#' + formId_).trigger("onChange", {"value": value_});
    if ((value_ != undefined && value_.length != 0) && $('#' + formId_ + '-' + value_)) $('#' + formId_ + '-selectionoutoflist').hide(); else $('#' + formId_ + '-selectionoutoflist').show();
  } else if (forceRefresh_) {
    $('.subscribers-' + formId_).trigger('masterRefresh');
  }
}

function TGrid_updateMarkerValue(formId_, value_) {
  let markerBatchId = $('#' + formId_).data('marker-batch-id');
  $('table[data-marker-batch-id="' + markerBatchId + '"] tr').removeClass('marked');
  $('#' + formId_ + ' tr[data-marker-value="' + value_ + '"]').addClass('marked');
  if (value_ !== undefined && value_.length !== 0) {
    $('table[data-marker-batch-id="' + markerBatchId + '"]').each(function () {
      if ($(this).attr('id') !== formId_) {
        let cFormId = $(this).attr('id');
        let markerStrategy = $(this).data('marker-strategy');
        let scrollToMarker = $(this).data('scroll-to-marker');
        if (markerStrategy === 'ExactMatch') {
          $(this).find('tbody [data-marker-value="' + value_ + '"]').addClass('marked');
        } else {
          let rows = $(this).find('tbody tr').get(); // sorbarendezni a sorokat fuggetlenul a grid rendezettsegetol
          rows.sort(function (a, b) {
            if ($(a).data('marker-value') === $(b).data('marker-value')) return 0;
            if ($(a).data('marker-value') < $(b).data('marker-value')) return -1;
            return 1;
          });
          let lastMarkerValue = '';
          if (markerStrategy === 'LastSmallerOrEqual') {
            $.each(rows, function () {
              let cMarkerValue = $(this).data('marker-value');
              if (cMarkerValue !== undefined && cMarkerValue.length !== 0) {
                if (cMarkerValue === value_) {
                  lastMarkerValue = cMarkerValue;
                  return false;
                } else if (cMarkerValue > value_) {
                  return false;
                }
                lastMarkerValue = cMarkerValue;
              }
            });
          } else if (markerStrategy === 'LastLargerOrEqual') {
            $.each(rows.reverse(), function () {
              let cMarkerValue = $(this).data('marker-value');
              if (cMarkerValue !== undefined && cMarkerValue.length !== 0) {
                if (cMarkerValue === value_) {
                  lastMarkerValue = cMarkerValue;
                  return false;
                } else if (cMarkerValue < value_) {
                  return false;
                }
                lastMarkerValue = cMarkerValue;
              }
            });
          }
          if (lastMarkerValue !== '') {
            $('#' + cFormId + ' tr[data-marker-value="' + lastMarkerValue + '"]').addClass('marked');
            if (scrollToMarker) {
              let rowpos = $('#data_' + cFormId + ' tr[data-marker-value="' + lastMarkerValue + '"]:first').position();
              console.log('scroll to:',rowpos);
              $('#data_' + cFormId).scrollTop(rowpos.top-27);
            }
          }
        }
      }
    });

  }
}

/*function TGrid_refresh(formId_,urldata) {                                          // grid refresh
    TGrid_submit(formId_,urldata);
}*/

function TGrid_setSorting(formId_, sortedBy_, sortingDirection_) {      // sorbarendezes
  $("#helper_" + formId_ + " #TGrid_SortedBy_").val(sortedBy_);                  // rendezendo oszlop
  $("#helper_" + formId_ + " #TGrid_SortingDirection_").val(sortingDirection_);            // rendezes iranya
  // TGrid_submit(formId_);
  Tholos.eventHandler(formId_, formId_, 'TGrid', 'refresh');
}

function TGrid_setPage(formId_, pageNumber_) {                             // lapozas
  $("#helper_" + formId_ + " #TGrid_ActivePage_").val(pageNumber_);
  Tholos.eventHandler(formId_, formId_, 'TGrid', 'refresh');
}

function TGrid_setRowsPerPage(formId_, rowsPerPage_) {                     // megjelenitett sorok szama
  $("#helper_" + formId_ + " #TGrid_RowsPerPage_").val(rowsPerPage_);
  $("#helper_" + formId_ + " #TGrid_ActivePage_").val(1);                                    // az elso lap mutatasa
  Tholos.eventHandler(formId_, formId_, 'TGrid', 'refresh');
}

function TGrid_setScrollable(formId_, scrollable_) {                     // scrollable X
  $("#helper_" + formId_ + " #TGrid_Scrollable_").val(scrollable_);
  Tholos.eventHandler(formId_, formId_, 'TGrid', 'refresh');
}

function TGrid_setScrollableY(formId_, scrollable_) {                     // scrollable Y
  $("#helper_" + formId_ + " #TGrid_ScrollableY_").val(scrollable_);
  Tholos.eventHandler(formId_, formId_, 'TGrid', 'refresh');
}

function TGrid_setTransposed(formId_, transposed_) {                     // transposed
  $("#helper_" + formId_ + " #TGrid_Transposed_").val(transposed_);
  Tholos.eventHandler(formId_, formId_, 'TGrid', 'refresh');
}

/* filters */
function TGrid_showFilterParameters(modalId_, filterDatatype_, filterListSource_, filterListSourceRoute_, filterListFilter_, filterFieldId_, filterFieldText_) {
  $(modalId_).find(".TGrid-filter-" + filterDatatype_).show(); // adattipusnak megfelelo container megjelenitese
  $(modalId_ + " .TGrid-filter-" + filterDatatype_).trigger("TGrid.show", {
    filterListSource: filterListSource_,
    filterListSourceRoute: filterListSourceRoute_,
    filterListFilter: filterListFilter_,
    filterFieldId: filterFieldId_,
    filterFieldText: filterFieldText_
  });   // udatatable show esemeny letrehozasa
}

function TGrid_findEmptyFilterSlot(formId_) {
  for (var i = 1; true; i++) if (!($("#helper_" + formId_ + " #" + Tholos.getComponentName(formId_) + "_f_" + i).length > 0)) return i;  // ures input mezo keresese
}

function TGrid_regenerateTextFilters(formId_, filterSlot_) {
  if (filterSlot_ > 0) $("#helper_" + formId_ + " #filterslot" + filterSlot_).remove();
  for (var i = 1; i < 100; i++) {
    if ((filterSlot_ > 0 && i == filterSlot_) || (filterSlot_ < 1 && $("#helper_" + formId_ + " #" + Tholos.getComponentName(formId_) + "_f_" + i).length > 0)) {                               // ha van ilyen filter, akkor
      const filterParams = TGrid_explode(":", $("#helper_" + formId_ + " #" + Tholos.getComponentName(formId_) + "_f_" + i).val(), 3);
      //var filters = eval("TGrid_filters_" + formId_);
      const filters = window["TGrid_filters_" + formId_];
      for (let j = 0; j < filters.length; j++) if (filters[j].id == filterParams[0]) var filter = filters[j];

      var textFilter = '<span class="badge badge-sm badge-info" style="margin-bottom: 3px;" id="filterslot' + i + '">' +  // filterslot
        '<a href="javascript:TGrid_setFilter(\'' + formId_ + '\',\'' + filter.id + '\',' + i + ');" style="color: white;">' +                                             // szerkeszto link
        filter.name + ' ' +
        $("#filter_container_" + formId_ + " #operator option[value='" + filterParams[1] + "']").text() + ' ' +             // operator-hoz tartozo szoveg
        ((filterParams[1] == "NULL" || filterParams[1] == "NOT NULL") ? "" :
          (TGrid_inArray(filter.datatype, ['bool', 'boolIN', 'boolYN', 'bool10']) ? $("#filter_container_" + formId_ + " .TGrid-filter-" + filter.datatype + " .value1 option[value='" + filterParams[2] + "']").text() : filterParams[2])) + // ha nem NULL vagy NOT NULL, akkor ertek megjelenitese
        '</a>' +
        '&nbsp;&nbsp;&nbsp;' +
        '<span style="cursor: pointer" onclick="TGrid_removeFilter(\'' + formId_ + '\',\'' + filter.id + '\',' + i + ');">' +                                            // filter torlese link
        '<i class="fa-regular fa-trash-can text-white"></i></span></span>&nbsp;';
      $("#helper_" + formId_ + " #filters_text").append(textFilter);                 // szoveges filterslot letrehozasa
    }
  }
  if ($("#helper_" + formId_ + " [id^=" + Tholos.getComponentName(formId_) + "_f_]").length > 0)                                // ha van filter, akkor a no-filter szoveg eltuntetese
    $("#helper_" + formId_ + " #filters_text #filterslot-nofilter").hide();
  else $("#helper_" + formId_ + " #filters_text #filterslot-nofilter").show();
  if (filterSlot_ > -1) {
    if ($("#" + formId_ + "-props").data().autofilterrefresh)
      Tholos.eventHandler($("#" + formId_ + "-props").data().id, $("#" + formId_ + "-props").data().id, 'TGrid', 'refresh');
    else $("#helper_" + formId_ + " #filters_text #filter_refresh").show();  // refresh ikon megjelenitese
  }
}

function TGrid_cancelFilter(formId_) {
  $("#filter_container_" + formId_).hide();
  $("#header_" + formId_).show();
}

function TGrid_setFilter(formId_, filterId_, slotId_) { // modal - deprecated
  // editmode
  if (slotId_ > 0) {
    var filterParams = TGrid_explode(":", $("#helper_" + formId_ + " #" + Tholos.getComponentName(formId_) + "_f_" + slotId_).val(), 3);  // ha szerkesztes, akkor a parameterek felolvasa az input mezobol, formatum: filterid:operator:ertek
  }
  var filters = eval("TGrid_filters_" + formId_);
  for (var j = 0; j < filters.length; j++) if (filters[j].id == filterId_) filter = filters[j];
  // hide all filterDivs
  var fc = $("#filter_container_" + formId_);                                        // modalis ablak letrehozasa
  fc.find(".TGrid-filter-container").hide();                  // az osszes adattipushoz tartozo container eltuntetese
  TGrid_showFilterParameters("#filter_container_" + formId_, filter.datatype, filter.listsource, filter.listsourceroute, filter.listfilter, filter.fieldid, filter.fieldtext);          // az adattipusnak megfelelo container megjelenitese
  // $("#modal_" + formId_).modal({backdrop: 'static'});                              // modalis legyen pop-up
  fc.find('.filter-title').text(filter.name);                                  // dialogus cime a filter neve
  var filterValue = "";
  fc.find(".setbutton").off("click");                                         // elozo click esemeny eltavolitasa
  fc.find("#operator").off("change");                                         // operator legordulo change esemeny eltavolitasa
  // hiding operators
  $("#filter_container_" + formId_ + " #operator option").hide();
  $("#filter_container_" + formId_ + " #operator option." + filter.datatype).show();
  if (filter.canbenull && !TGrid_inArray(filter.datatype, ['bool', 'boolYN', 'boolIN', 'bool10', 'datebetween'])) {
    fc.find("#operator option[value='NULL']").show();
    fc.find("#operator option[value='NOT NULL']").show();
  }
  fc.find("#operator").change(function () {
    if (fc.find("#operator").val() == "NULL" || fc.find("#operator").val() == "NOT NULL") fc.find(".TGrid-filter-" + filter.datatype).hide(); else fc.find(".TGrid-filter-" + filter.datatype).show();
  });

  fc.find("#operator").val($("#filter_container_" + formId_ + " #operator option." + filter.datatype).filter(":first").val());
  fc.find("#operator").change();

  if (slotId_ > 0) {                                                             // ha szerkesztes, akkor
    fc.find("#operator").val(filterParams[1]);                                // operator legordulo beallitasa
    fc.find("#operator").change();                                            // ha NULL, akkor hide-olasa a mezonek
    $("#filter_container_" + formId_ + ' .TGrid-filter-' + filter.datatype + ' .value1').val(filterParams.length > 1 ? filterParams[2] : "");
    //console.log(filterParams);
    // value beallitasa
    // TODO ha TDateTimePicker, akkor nem elég az input mezőnek értéket adni, hanem a tempusdominus objektumnak is kell
    if (filterParams.length > 1 && TGrid_inArray(filter.datatype, ['datetime', 'datetimehm', 'date'])) {
      console.log(formId_);
      console.log(filterParams[2]);
      TholosDPArray[formId_ + '-' + filter.datatype + '-dtpicker'].dates.setFromInput(filterParams[2]);
    }
  } else {
    $("#filter_container_" + formId_ + ' .TGrid-filter-' + filter.datatype + ' .value1').val(""); // value torlese
  }
  fc.find(".setbutton").on("click", function (event) {                     // mentes gomb esemenye
    $("#filter_container_" + formId_).hide();                                          // modalis ablak eltuntetese
    var filterFieldValue = $("#filter_container_" + formId_ + " .TGrid-filter-" + filter.datatype + " .value1").val();
    // LIKE vagy NLIKE operator eseten ha nem hasznal wildcardokat a string-ben akkor ele es mogeteszunk egy % jelet
    if ((fc.find("#operator").val() == "like" || fc.find("#operator").val() == "nlike") && (filterFieldValue.indexOf("%")) === -1) {
      filterFieldValue = "%" + filterFieldValue + "%";
    }
    var filterValue = filterId_ + ":" + fc.find("#operator").val() + ((fc.find("#operator").val() == "NULL" || fc.find("#operator").val() == "NOT NULL") ? "" : (":" + filterFieldValue));
    // ertek bellitasa attol fuggone, hogy az operator NULL, NOT NULL vagy egyeb
    var filterSlot = 1;                                                            // alapertek
    if (slotId_ > 0) {                                                           // ha szerkesztes, akkor
      $("#helper_" + formId_ + " #" + Tholos.getComponentName(formId_) + "_f_" + slotId_).val(filterValue);                     // filter ertek beallitasa
      $("#helper_" + formId_ + " #filterslot" + slotId_).remove();                     // eredeti filterslot eltavolitasa
      filterSlot = slotId_;
    } else {
      filterSlot = TGrid_findEmptyFilterSlot(formId_);                       // ures filterslot keresese
      $("#helper_" + formId_).append($('<input>').attr({                           // input hidden mezo letrehozasa
        type: 'hidden',
        id: Tholos.getComponentName(formId_) + '_f_' + filterSlot,
        name: Tholos.getComponentName(formId_) + '_f_' + filterSlot,
        value: filterValue
      }));
    }
    $("#header_" + formId_).show();
    // reset page number to 1
    $("#helper_" + formId_ + " #TGrid_ActivePage_").val(1);
    TGrid_regenerateTextFilters(formId_, filterSlot);                       // regenerate text filters
  });
  $("#header_" + formId_).hide();
  fc.show();                                                           // modalis ablak megjelenitese
}

function TGrid_removeFilter(formId_, filterId_, slotId_) {
  $("#helper_" + formId_ + " #" + Tholos.getComponentName(formId_) + "_f_" + slotId_).remove();                                 // hidden form mezo torlese
  $("#helper_" + formId_ + " #filterslot" + slotId_).remove();                         // szoveges filterslot torlese
  if ($("#helper_" + formId_ + " [id^=" + Tholos.getComponentName(formId_) + "_f_]").length > 0)                                // ha van meg filter, akkor a no-filter szoveg eltuntetese
    $("#helper_" + formId_ + " #filters_text #filterslot-nofilter").hide();
  else $("#helper_" + formId_ + " #filters_text #filterslot-nofilter").show();
  if ($("#" + formId_ + "-props").data().autofilterrefresh)
    Tholos.eventHandler($("#" + formId_ + "-props").data().id, $("#" + formId_ + "-props").data().id, 'TGrid', 'refresh');
  else $("#helper_" + formId_ + " #filters_text #filter_refresh").show();    // refresh ikon megjelenitese
}

function TGrid_showDetails(formId_, urldata) {
  var modal = $("#details_" + formId_).modal();
  $("#details_" + formId_ + ' .modal-body').html('');
  modal.modal('show');
  var item = {};
  item.name = "TGrid_todo_";
  item.value = "details";
  urldata.push(item);
  $.ajaxq(
    (Tholos.getData(formId_).hasOwnProperty('ajaxqueueid') ? Tholos.getData(formId_)['ajaxqueueid'] : formId_),
    {
      url: $("#helper_" + formId_).attr('action'),
      type: 'post',
      dataType: 'json',
      contentType: "application/x-www-form-urlencoded;charset=UTF-8",
      data: $.param(urldata),
      success: function (data) {
        if (data.success == 'OK')
          $("#details_" + formId_ + ' .modal-body').html(data.html);                // grid megjelenitese
        else alert(data.errormsg);
      }
    });
}

function TGrid_download(formId_, urldata, type_) {
  var oaction = $("#helper_" + formId_).prop("action");
  $("#helper_" + formId_).prop("action", oaction + '?' + $.param(urldata));
  $("#helper_" + formId_ + " #TGrid_todo_").val(type_);
  $("#helper_" + formId_).prop("target", type_);
  $("#helper_" + formId_).prop("method", 'post');
  $("#helper_" + formId_).submit();
  $("#helper_" + formId_).prop("action", oaction);
  $("#helper_" + formId_ + " #TGrid_todo_").val('');
  $("#helper_" + formId_ + " #TGrid_dataparameters_").val('');
  $("#helper_" + formId_).prop("target", '');
  $("#helper_" + formId_).prop("method", 'get');
}

function TGrid_ready(formId_) {
  var sd = Tholos.getData(formId_);
  if ($("#helper_" + formId_ + " #TGrid_uuid_").val() != Tholos.getSessionStorageID() && sd.persistent != undefined && sd.persistent != "") {
    $("#helper_" + formId_ + " #TGrid_uuid_").val(Tholos.getSessionStorageID());
    TGrid_reloadPreviousState(formId_);
  } else {
    TGrid_regenerateTextFilters(formId_, -1);                // kirakni a text filtereket
    $("#helper_" + formId_ + " #filters_text #filter_refresh").hide();
    if (sd.ajaxmode && sd.autoload && !sd.datagenerated && (sd.master === undefined || sd.master == "")) {
      $("#helper_" + formId_ + " #TGrid_ViewMode_").val($("#helper_" + formId_ + " #TGrid_ViewModeInit_").val());
      Tholos.eventHandler(formId_, formId_, 'TGrid', 'refresh');
    }
  }
  $("#" + formId_ + "-props").off("masterDataChange");
  $("#" + formId_ + "-props").on("masterDataChange", function (e, edata) {
    $("#helper_" + $(this).data().id + " #TGrid_MasterValue_").val(edata);
    $("#helper_" + $(this).data().id + " #TGrid_ActivePage_").val(1);
    Tholos.eventHandler($(this).data().id, $(this).data().id, 'TGrid', 'refresh');
  });
}

$(document).ready(function () {
  $("iframe.autoresize").each(function () {
    $(this).height($(this).contents().find("body").height());
  });
});