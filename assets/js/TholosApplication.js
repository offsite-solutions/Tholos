jQuery.expr[':'].regex = function (elem, index, match) {
  var matchParams = match[3].split(','),
    validLabels = /^(data|css):/,
    attr = {
      method: matchParams[0].match(validLabels) ?
        matchParams[0].split(':')[0] : 'attr',
      property: matchParams.shift().replace(validLabels, '')
    },
    regexFlags = 'ig',
    regex = new RegExp(matchParams.join('').replace(/^s+|s+$/g, ''), regexFlags);
  return regex.test(jQuery(elem)[attr.method](attr.property));
};

var TholosLastClickType = "left";

var TholosDPArray = {};

var Tholos = {
  methods: {
    TComponent_parseControlParameters: function (sender, target, route, eventData) {
      Tholos.trace("TComponent_parseControlParameters()", sender, target, route, eventData);
      if (eventData) {
        var targetType = Tholos.getComponentType(target);
        $.each(eventData, function (ctrlName, ctrlParam) {
          if (ctrlName.slice(-2) === "()") {
            // General function handling when control contains opening/closing brackets ()
            Tholos.eventHandler(sender, target, targetType, ctrlName.slice(0, -2), route, eventData, ctrlParam);
          } else {
            // Otherwise we treat this as a property and trying to run the corresponding set function
            var oParam = {};
            oParam[ctrlName] = ctrlParam;
            if (ctrlName === "readonly") {
              ctrlName = "readOnly";
            } else if (ctrlName === "errormsg") {
              ctrlName = "errorMsg";
            }
            var ret = Tholos.eventHandler(sender, target, targetType, "set" + ctrlName.charAt(0).toUpperCase() + ctrlName.slice(1), route, eventData, oParam);
          }
        });
      }
    },

    TComponent_setDataParameters: function (sender, target, route, eventData) {
      Tholos.trace("TComponent_setDataParameters()", sender, target, route, eventData);
      if (eventData) {
        var td = Tholos.getData(target);
        if (!td.dataparameters) {
          Tholos.setData(target, "dataparameters", eventData);
        } else {
          // merge data parameters
          jQuery.extend(td.dataparameters, eventData);
          Tholos.setData(target, "dataparameters", td.dataparameters);
        }
        Tholos.action(true, sender, target);
      }
    },
    TAction_navigate: function (sender, target, route, eventData) {
      Tholos.debug("TAction_navigate()", sender, target, route, eventData);
      var sd = Tholos.getData(sender);
      var urlparams = "";
      if (sd.dataparameters) {
        urlparams = Tholos.EncodeQueryData(sd.dataparameters);
      }
      if (eventData) {
        //urlparams = (urlparams.length>0?'&':'')+Tholos.EncodeQueryData(eventData);
      }
      if (TholosLastClickType == "left") {
        Tholos.pageLoader(true, (eventData.loader !== undefined && eventData.loader) ? true : false);
        document.location.href = route + "/?" + urlparams;
      } else if (TholosLastClickType == "middle") {
        window.open(route + "/?" + urlparams, "_blank");
      }
      TholosLastClickType = "left";
    },
    TControl_getValue: function (sender, target, route, eventData) {
      Tholos.trace("TControl_getValue()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      return o.val();
    },
    TLOV_getValue: function (sender, target, route, eventData) {
      Tholos.trace("TLOV_getValue()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      var value = o.val();
      if (value instanceof Array) {
        if (value.length === 0) {
          return null;
        }
        // skip empty values - select2 bug fix
        return JSON.stringify(value.filter(function (item) {
          return item != null && item !== "";
        }));
      }
      return value;
    },
    TGrid_getValue: function (sender, target, route, eventData) {
      Tholos.trace("TGrid_getValue()", sender, target, route, eventData);
      var d = Tholos.getData(target);
      return d.value;
    },
    TGrid_getFilterSQL: function (sender, target, route, eventData) {
      Tholos.trace("TGrid_getFilterSQL()", sender, target, route, eventData);
      return $("#helper_" + target + " #TGrid_FilterSQL_").val();
    },
    TCheckbox_getValue: function (sender, target, route, eventData) {
      Tholos.trace("TCheckbox_getValue()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      var d = Tholos.getData(target);

      return (o.prop("checked") ? d.valuechecked : d.valueunchecked);
    },
    TRadio_getValue: function (sender, target, route, eventData) {
      Tholos.trace("TRadio_getValue()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      var d = Tholos.getData(target);

      var ret = $(o).find("input[name='" + d.name + "']:checked").val();
      return (ret === undefined ? "" : ret);
    },
    TForm_setEnabled: function (sender, target, route, eventData) {
      Tholos.trace("TForm_setEnabled()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      if (eventData.enabled === "false") {
        o.find("fieldset").attr("disabled", "");
      } else {
        o.find("fieldset").prop("disabled", false);
      }
      Tholos.setData(target, "enabled", eventData.enabled);
      return true;
    },
    TComponent_setEnabled: function (sender, target, route, eventData) {
      Tholos.trace("TComponent_setEnabled()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      if (eventData.enabled === "false") {
        o.attr("disabled", "disabled");
      } else {
        o.prop("disabled", false);
      }
      Tholos.setData(target, "enabled", eventData.enabled);
      return true;
    },
    TTimer_setEnabled: function (sender, target, route, eventData) {
      Tholos.trace("TTimer_setEnabled()", sender, target, route, eventData);
      Tholos.setData(target, "enabled", eventData.enabled);
      return true;
    },
    TRadio_setEnabled: function (sender, target, route, eventData) {
      Tholos.trace("TRadio_setEnabled()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      var d = Tholos.getData(target);

      if (eventData.enabled === "false") {
        o.find("input[name='" + d.name + "']").attr("disabled", "disabled");
      } else {
        o.find("input[name='" + d.name + "']").prop("disabled", false);
      }
      Tholos.setData(target, "enabled", eventData.enabled);
      return true;
    },
    TControl_setLabel: function (sender, target, route, eventData) {
      Tholos.trace("TControl_setLabel()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      if (eventData.label) {
        $("label[for='" + o.attr("id") + "']").html(eventData.label);
      }
      return true;
    },
    TControl_setRequired: function (sender, target, route, eventData) {
      Tholos.trace("TControl_setRequired()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      var d = Tholos.getData(target);
      if (eventData.required === "true") {
        o.attr("required", "");
        o.closest('div.row').find("#" + d.id + "-label").addClass("required");
      } else {
        o.prop("required", false);
        o.closest('div.row').find("#" + d.id + "-label").removeClass("required");
      }
      Tholos.setData(target, "required", eventData.required);
      return true;
    },
    TCheckbox_setRequired: function (sender, target, route, eventData) {
      //Checkbox is always required
      return true;
    },
    TControl_setVisible: function (sender, target, route, eventData) {
      Tholos.trace("TControl_setVisible()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      if (eventData.visible === "true") {
        o.show();
        Tholos.trace("TControl_setVisible: Triggering onShow");
        o.trigger("onShow");
      } else {
        o.hide();
        Tholos.trace("TControl_setVisible: Triggering onHide");
        o.trigger("onHide");
      }
      Tholos.setData(target, "visible", eventData.visible);
      return true;
    },
    TControl_getVisible: function (sender, target, route, eventData) {
      Tholos.trace("TControl_getVisible()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      return (o.is(":visible") ? "true" : "false");
    },
    TFormControl_setVisible: function (sender, target, route, eventData) {
      Tholos.trace("TFormControl_setVisible()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      if (eventData.visible === "true") {
        o.closest("div.row").show();
        o.closest("div.row").css('display', 'flex');
        Tholos.trace("TFormControl_setVisible: Triggering onShow");
        o.trigger("onShow");
      } else {
        o.closest("div.row").hide();
        Tholos.trace("TFormControl_setVisible: Triggering onHide");
        o.trigger("onHide");
      }
      Tholos.setData(target, "visible", eventData.visible);
      return true;
    },
    TGrid_setVisible: function (sender, target, route, eventData) {
      Tholos.trace("TGrid_setVisible()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      if (eventData.visible === "true") {
        $("#container_" + target).show();
        Tholos.trace("TGrid_setVisible: Triggering onShow");
        o.trigger("onShow");
      } else {
        $("#container_" + target).hide();
        Tholos.trace("TGrid_setVisible: Triggering onHide");
        o.trigger("onHide");
      }
      Tholos.setData(target, "visible", eventData.visible);
      return true;
    },
    TContainer_setVisible: function (sender, target, route, eventData) {
      Tholos.trace("TContainer_setVisible()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      if (eventData.visible === "true") {
        o.show();
        Tholos.trace("TContainer_setVisible(): Triggering onShow");
        o.trigger("onShow");
      } else {
        o.hide();
        Tholos.trace("TContainer_setVisible(): Triggering onHide");
        o.trigger("onHide");
      }
      Tholos.setData(target, "visible", eventData.visible);
      return true;
    },
    TContainer_getVisible: function (sender, target, route, eventData) {
      Tholos.trace("TControl_getVisible()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      return (o.is(":visible") ? "true" : "false");
    },
    TLabel_setVisible: function (sender, target, route, eventData) {
      Tholos.trace("TLabel_setVisible()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      if (eventData.visible === "true") {
        o.show();
        Tholos.trace("TLabel_setVisible(): Triggering onShow");
        o.trigger("onShow");
      } else {
        o.hide();
        Tholos.trace("TLabel_setVisible(): Triggering onHide");
        o.trigger("onHide");
      }
      Tholos.setData(target, "visible", eventData.visible);
      return true;
    },
    TImage_setVisible: function (sender, target, route, eventData) {
      Tholos.trace("TImage_setVisible()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      if (eventData.visible === "true") {
        o.show();
        Tholos.trace("TImage_setVisible: Triggering onShow");
        o.trigger("onShow");
      } else {
        o.hide();
        Tholos.trace("TImage_setVisible: Triggering onHide");
        o.trigger("onHide");
      }
      Tholos.setData(target, "visible", eventData.visible);
      return true;
    },
    TFormControl_setFocus: function (sender, target, route, eventData) {
      Tholos.trace("TFormControl_setFocus()", sender, target, route, eventData);
      var o = Tholos.getObject(target)
      o.focus();
      return true;
    },
    TContainer_show: function (sender, target, route, eventData) {
      var targetType = Tholos.getComponentType(target);
      Tholos.eventHandler(sender, target, targetType, "setVisible", route, eventData, {"visible": "true"});
    },
    TContainer_hide: function (sender, target, route, eventData) {
      var targetType = Tholos.getComponentType(target);
      Tholos.eventHandler(sender, target, targetType, "setVisible", route, eventData, {"visible": "false"});
    },
    TContainer_setLoadable: function (sender, target, route, eventData) {
      Tholos.trace("TContainer_setLoadable()", sender, target, route, eventData);
      Tholos.setData(target, "loadable", eventData.loadable);
      return true;
    },
    TControl_setReadOnly: function (sender, target, route, eventData) {
      Tholos.trace("TControl_setReadOnly()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      if (eventData.readonly === "true") {
        o.attr("readonly", "readonly");
      } else {
        o.prop("readonly", false);
      }
      Tholos.setData(target, "readonly", eventData.readonly);
      return true;
    },
    TCheckbox_setReadOnly: function (sender, target, route, eventData) {
      Tholos.trace("TCheckbox_setReadOnly()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      if (eventData.readonly === "true") {
        o.attr("disabled", "disabled");
      } else {
        o.prop("disabled", false);
      }
      Tholos.setData(target, "readonly", eventData.readonly);
      return true;
    },
    TDateTimePicker_setReadOnly: function (sender, target, route, eventData) {
      Tholos.trace("TDateTimePicker_setValue()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      var d = Tholos.getData(target);
      if (eventData.readonly === "true") {
        o.attr("readonly", "readonly");
        TholosDPArray[d.id].disable();
      } else {
        o.prop("readonly", false);
        TholosDPArray[d.id].enable();
      }
      Tholos.setData(target, "readonly", eventData.readonly);
      return true;
    },
    THTMLEdit_setReadOnly: function (sender, target, route, eventData) {
      Tholos.trace("THTMLEdit_setReadOnly()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      var d = Tholos.getData(target);
      if (eventData.readonly === "true") {
        o.attr("readonly", "readonly");
      } else {
        o.prop("readonly", false);
      }
      Tholos.setData(target, "readonly", eventData.readonly);
      window['tholos_rte_' + d.id].setReadOnly(eventData.readonly === "true");
      return true;
    },
    TLOV_setReadOnly: function (sender, target, route, eventData) {
      Tholos.trace("TLOV_setReadOnly()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      if (eventData.readonly === "true") {
        o.attr("disabled", "disabled");
      } else {
        o.prop("disabled", false);
      }
      Tholos.setData(target, "readonly", eventData.readonly);
      return true;
    },
    TControl_setValue: function (sender, target, route, eventData) {
      Tholos.trace("TControl_setValue()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      Tholos.setData(target, "value", eventData.value);
      Tholos.trace("TControl_setValue(): Triggering change");
      o.val(eventData.value).trigger("change");
      return true;
    },
    TCheckbox_setValue: function (sender, target, route, eventData) {
      Tholos.trace("TCheckbox_setValue()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      var d = Tholos.getData(target);
      Tholos.setData(target, "value", eventData.value);
      Tholos.trace("TCheckbox_setValue(): Triggering change");
      if (typeof d.valuechecked === "boolean") {
        o.prop("checked", eventData.value === "true").trigger("change");
      } else {
        o.prop("checked", (d.valuechecked === eventData.value)).trigger("change");
      }
      //console.log(d.valuechecked, eventData.value, (d.valuechecked === eventData.value));
      return true;
    },
    TRadio_setValue: function (sender, target, route, eventData) {
      Tholos.trace("TRadio_setValue()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      var d = Tholos.getData(target);
      if (eventData.value) {
        Tholos.setData(target, "value", eventData.value);
        Tholos.trace("TRadio_setValue(): Triggering change");
        o.find("input[name='" + d.name + "'][value='" + eventData.value + "']").prop("checked", true).trigger("change");
      }
      return true;
    },
    TStatic_setValue: function (sender, target, route, eventData) {
      Tholos.trace("TStatic_setValue()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      Tholos.setData(target, "value", eventData.value);
      o.parent().find('.form-control-plaintext').html(eventData.value);
      Tholos.trace("TStatic_setValue(): Triggering change");
      o.val(eventData.value).trigger("change");
      return true;
    },
    TGrid_setValue: function (sender, target, route, eventData) {
      Tholos.trace("TGrid_setValue()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      Tholos.setData(target, "value", eventData.value);
      TGrid_updateSelection(target, eventData.value);
    },
    TGrid_setValueForce: function (sender, target, route, eventData) {
      Tholos.trace("TGrid_setValue()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      Tholos.setData(target, "value", eventData.value);
      TGrid_updateSelection(target, eventData.value, true);
    },
    TGrid_setValueRefresh: function (sender, target, route, eventData) {
      Tholos.trace("TGrid_setValue()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      Tholos.setData(target, "value", eventData.value);
      TGrid_updateSelection(target, eventData.value, true);
      Tholos.eventHandler(sender, target, 'TGrid', 'refresh', route, eventData);
    },
    TGrid_setMarkerValue: function (sender, target, route, eventData) {
      Tholos.trace("TGrid_setMarkerValue()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      Tholos.setData(target, "markerValue", eventData.value);
      TGrid_updateMarkerValue(target, eventData.markerValue);
    },
    TImage_setValue: function (sender, target, route, eventData) {
      Tholos.trace("TImage_setValue()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      Tholos.setData(target, "value", eventData.value);
      o.prop("src", eventData.value);
      return true;
    },
    TDateTimePicker_setValue: function (sender, target, route, eventData) {
      Tholos.trace("TDateTimePicker_setValue()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      var d = Tholos.getData(target);
      Tholos.setData(target, "value", moment(eventData.value, 'YYYY-MM-DD HH:mm:ss').format(d.jsdatetimeformat.replace('yyyy', 'YYYY').replace('dd', 'DD')));
      // TODO test from callback
      TholosDPArray[d.id].dates.setValue(tempusDominus.DateTime.convert(moment(eventData.value, 'YYYY-MM-DD HH:mm:ss').toDate()));
      Tholos.trace("TControl_setValue(): Triggering change");
      o.val(moment(eventData.value, 'YYYY-MM-DD HH:mm:ss').format(d.jsdatetimeformat.replace('yyyy', 'YYYY').replace('dd', 'DD'))).trigger("change");
      return true;
    },
    TControl_setErrorMsg: function (sender, target, route, eventData) {
      Tholos.trace("TControl_setErrorMsg()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      if (eventData.errormsg) {
        o.closest(".form-group").addClass("has-error");
        o.siblings(".help-block").html(eventData.errormsg).show();
      }
      return true;
    },
    TControl_click: function (sender, target, route, eventData) {
      Tholos.trace("TControl_click()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      Tholos.trace("TControl_click(): Triggering click");
      o.trigger("click", [eventData]);
      return true;
    },
    TGrid_initialize: function (sender, target, route, eventData) {
      Tholos.trace("TGrid_initialize()", sender, target, route, eventData);
      var sd = Tholos.getData(target);
      if (!sd.datagenerated) Tholos.eventHandler(sender, target, 'TGrid', 'refresh', route, eventData);
    },
    TGrid_refresh: function (sender, target, route, eventData) {
      Tholos.trace("TGrid_refresh()", sender, target, route, eventData);
      var sd = Tholos.getData(target); // a grid dataparametereinek (nem csak a hivonak) az atadasa
      var o = Tholos.getObject(target);
      if ($(o).is(":hidden") && !sd.refreshwhenhidden) return true;

      // Merging local and global eventData variables
      if (sd && sd.dataparameters) {
        for (var k in sd.dataparameters) {
          eventData[k] = sd.dataparameters[k];
        }
      }

      var urldata = [];
      for (var k in eventData) {
        var item = {};
        item.name = k;
        item.value = eventData[k];
        urldata.push(item);
      }

      TGrid_submit(sender, target, urldata);
    },
    TGrid_showDetails: function (sender, target, route, eventData) {
      Tholos.trace("TGrid_showDetails()", sender, target, route, eventData);
      var urldata = [];
      for (var k in eventData) {
        var item = {};
        item.name = k;
        item.value = eventData[k];
        urldata.push(item);
      }
      TGrid_showDetails(target, urldata);
    },
    TGrid_downloadExcel: function (sender, target, route, eventData) {
      Tholos.trace("TGrid_downloadExcel()", sender, target, route, eventData);
      var sd = Tholos.getData(target); // a grid dataparametereinek (nem csak a hivonak) az atadasa

      // Merging local and global eventData variables
      if (sd && sd.dataparameters) {
        for (var k in sd.dataparameters) {
          eventData[k] = sd.dataparameters[k];
        }
      }

      var urldata = [];
      for (var k in eventData) {
        var item = {};
        item.name = k;
        item.value = eventData[k];
        urldata.push(item);
      }
      TGrid_download(target, urldata, 'excel');
    },
    TGrid_downloadTSV: function (sender, target, route, eventData) {
      Tholos.trace("TGrid_downloadTSV()", sender, target, route, eventData);
      var sd = Tholos.getData(target); // a grid dataparametereinek (nem csak a hivonak) az atadasa

      // Merging local and global eventData variables
      if (sd && sd.dataparameters) {
        for (var k in sd.dataparameters) {
          eventData[k] = sd.dataparameters[k];
        }
      }

      var urldata = [];
      for (var k in eventData) {
        var item = {};
        item.name = k;
        item.value = eventData[k];
        urldata.push(item);
      }

      TGrid_download(target, urldata, 'TSV');
    },
    TGrid_downloadCSV: function (sender, target, route, eventData) {
      Tholos.trace("TGrid_downloadTSV()", sender, target, route, eventData);
      var sd = Tholos.getData(target); // a grid dataparametereinek (nem csak a hivonak) az atadasa

      // Merging local and global eventData variables
      if (sd && sd.dataparameters) {
        for (var k in sd.dataparameters) {
          eventData[k] = sd.dataparameters[k];
        }
      }

      var urldata = [];
      for (var k in eventData) {
        var item = {};
        item.name = k;
        item.value = eventData[k];
        urldata.push(item);
      }

      TGrid_download(target, urldata, 'CSV');
    },
    TGrid_downloadRAWTSV: function (sender, target, route, eventData) {
      Tholos.trace("TGrid_downloadTSV()", sender, target, route, eventData);
      var sd = Tholos.getData(target); // a grid dataparametereinek (nem csak a hivonak) az atadasa

      // Merging local and global eventData variables
      if (sd && sd.dataparameters) {
        for (var k in sd.dataparameters) {
          eventData[k] = sd.dataparameters[k];
        }
      }

      var urldata = [];
      for (var k in eventData) {
        var item = {};
        item.name = k;
        item.value = eventData[k];
        urldata.push(item);
      }

      TGrid_download(target, urldata, 'RAWTSV');
    },
    TGrid_downloadRAWCSV: function (sender, target, route, eventData) {
      Tholos.trace("TGrid_downloadTSV()", sender, target, route, eventData);
      var sd = Tholos.getData(target); // a grid dataparametereinek (nem csak a hivonak) az atadasa

      // Merging local and global eventData variables
      if (sd && sd.dataparameters) {
        for (var k in sd.dataparameters) {
          eventData[k] = sd.dataparameters[k];
        }
      }

      var urldata = [];
      for (var k in eventData) {
        var item = {};
        item.name = k;
        item.value = eventData[k];
        urldata.push(item);
      }

      TGrid_download(target, urldata, 'RAWCSV');
    },
    TGrid_downloadRAWJSON: function (sender, target, route, eventData) {
      Tholos.trace("TGrid_downloadTSV()", sender, target, route, eventData);
      var sd = Tholos.getData(target); // a grid dataparametereinek (nem csak a hivonak) az atadasa

      // Merging local and global eventData variables
      if (sd && sd.dataparameters) {
        for (var k in sd.dataparameters) {
          eventData[k] = sd.dataparameters[k];
        }
      }

      var urldata = [];
      for (var k in eventData) {
        var item = {};
        item.name = k;
        item.value = eventData[k];
        urldata.push(item);
      }

      TGrid_download(target, urldata, 'RAWJSON');
    },
    TButton_click: function (sender, target, route, eventData) {
      Tholos.trace("TButton_click()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      o.trigger("click", [eventData]);
      return true;
    },
    TContainer_clearContent: function (sender, target, route, eventData) {
      Tholos.trace("TContainer_clearContent()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      o.html("");
      o.trigger("onContentCleared", []);
      Tholos.action(true, sender, target);
    },
    TContainer_loadContent: function (sender, target, route, eventData) {
      Tholos.trace("TContainer_loadContent()", sender, target, route, eventData);
      var urldata = [];
      // var s = Tholos.getData(sender);
      var ajaxqueueid = sender;
      for (var k in eventData) {
        var item = {};
        item.name = k;
        item.value = eventData[k];
        urldata.push(item);
      }
      // BS5 - select2 fix
      var o = Tholos.getObject(target);
      if (!eventData.TholosGUIModalScope && o.closest("div.modal").length > 0) {
        let item = {};
        item.name = 'TholosGUIModalScope';
        item.value = o.closest("div.modal").attr("id");
        urldata.push(item);
      }
      let d = Tholos.getData(target);
      if (d && d.hasOwnProperty('loadable') && (d.loadable === 'false' || d.loadable === false)) {
        Tholos.action(false, sender, target);
        return;
      }
      if (!eventData.sourceurl) {
        eventData.sourceurl = d.sourceurl;
        ajaxqueueid = d.hasOwnProperty('ajaxqueueid') ? d.ajaxqueueid : d.name;
      }
      if (!eventData.sourceformat) {
        eventData.sourceformat = "json";
      }
      if (eventData.clearcontent && eventData.clearcontent == "true") {
        Tholos.getObject(target).html("");
        Tholos.getObject(target).trigger("onContentCleared", []);
      }
      Tholos.pageLoader((eventData.loaderdisabled !== undefined && !eventData.loaderdisabled) ? true : false, (eventData.loader !== undefined && eventData.loader) ? true : false);
      $.ajaxq(ajaxqueueid,
        {
          url: eventData.sourceurl,
          type: "post",
          dataType: eventData.sourceformat,
          data: $.param(urldata),
          contentType: "application/x-www-form-urlencoded;charset=UTF-8",
          complete: function () {
            Tholos.pageLoader(false, false);
          },
          success: function (data) {
            let d = Tholos.getData(target); // check if loadable flag turned off while loading the page
            if (d && d.hasOwnProperty('loadable') && (d.loadable === 'false' || d.loadable === false)) {
              Tholos.action(false, sender, target);
              return;
            }
            if (eventData.sourceformat == "json" && data.html) {
              Tholos.getObject(target).html(data.html);
            } else if (eventData.sourceformat == "html") {
              Tholos.getObject(target).html(data);
            }
            Tholos.generateDocumentTitle();
            Tholos.getObject(target).trigger("onContentLoaded", []);
            Tholos.action(true, sender, target);
          },
          error: function (response, textStatus, errorThrown) {
            Tholos.handleJSONError(response, textStatus, errorThrown);
            Tholos.action(false, sender, target);
          }
        });
    },
    TModal_loadContent: function (sender, target, route, eventData) {
      Tholos.trace("TModal_loadContent()", sender, target, route, eventData);
      var d = Tholos.getData(target);
      var o = Tholos.getObject(target);
      if (!eventData.sourceurl) {
        eventData["sourceurl"] = d.sourceurl;
      }
      if (eventData.setTitle) {
        o.find(".modal-header .modal-title").html(eventData.setTitle);
      }
      eventData['TholosGUIModalScope'] = target; // BS5
      Tholos.eventHandler(sender, target, "TModal", "show", route, eventData);
      Tholos.eventHandler(sender, target + " .modal-body", "TContainer", "loadContent", route, eventData);
    },
    TModal_hide: function (sender, target, route, eventData) {
      Tholos.trace("TModal_hide()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      var modal = o.modal();
      modal.modal("hide");
      Tholos.action(true, sender, target);
    },
    TModal_handleOnHide: function (e) {
      var target = e.target.id;
      Tholos.trace("TModal_handleOnHide()", target);
      var d = Tholos.getData(target);
      var o = Tholos.getObject(target);
      var modal = o.modal();
      if (d.clearcontentonhide) {
        // TODO: battika okosabban kene ezt
        $("#" + target + " .modal-body").html("");
        o.trigger("onContentCleared", []);
      }
      Tholos.trace("TModal_handleOnHide(): Triggering onHide");
      o.trigger("onHide", []);
    },
    TModal_show: function (sender, target, route, eventData) {
      Tholos.trace("TModal_show()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      var modal = o.modal();
      modal.modal("show");
      Tholos.action(true, sender, target);
    },
    TModal_handleOnShow: function (e) {
      var target = e.target.id;
      Tholos.trace("TModal_handleOnShow()", target);
      var o = Tholos.getObject(target);
      Tholos.trace("TModal_handleOnShow(): Triggering onShow");
      o.trigger("onShow", []);
    },
    TFormContainer_hide: function (sender, target, route, eventData) {
      Tholos.trace("TFormContainer_hide()", sender, target, route, eventData);
      var d = Tholos.getData(target);
      var o = Tholos.getObject(target);
      if (d.clearcontentonhide) {
        o.html("");
        o.trigger("onContentCleared", []);
      }
      Tholos.trace("TFormContainer_hide(): Triggering onHide");
      o.trigger("onHide", []);
      Tholos.action(true, sender, target);
    },
    TFileUpload_upload: function (sender, target, route, eventData) {
      Tholos.trace("TFileUpload_upload()", sender, target, route, eventData);
      var d = Tholos.getData(target);
      var o = Tholos.findComponentId(target);
      var dz = $("#" + o + "-dz").get(0).dropzone;
      if (dz.files && dz.files.length > 0 && !d.uploadstatus) {
        dz.on("queuecomplete", Tholos.methods.TFileUpload_handleQueueComplete);
        dz.on("successmultiple", Tholos.methods.TFileUpload_handleSuccessMultiple);
        dz.processQueue();
        Tholos.setData(o, "uploadstatus", "started");
        Tholos.setData(o, "uploadsender", sender);

        if (Tholos.getComponentType(sender) === "TForm") {
          try {
            eventData = JSON.parse(eventData);
          } finally {
            Tholos.setData(o, "uploadsenderdata", JSON.stringify(eventData));
          }
        }
      } else {
        Tholos.trace("TFileUpload_upload() skip uploading files as already done or file list is empty", sender, target, route, eventData);
        Tholos.setData(o, "uploadstatus", "success");
        Tholos.setData(o, "value", "");
        $("#" + o).val("");
        if (Tholos.getComponentType(sender) === "TForm") {
          Tholos.trace("TFileUpload_upload(): upload will not start, calling TForm with original parameters");
          var callParams = JSON.parse(eventData);
          Tholos.eventHandler(callParams.sender, callParams.target, "TForm", "submit", callParams.route, callParams.eventData);
        }
      }
    },
    TFileUpload_handleAddedFile: function (f) {
      Tholos.trace("TFileUpload_handleAddedFile()", f);
      var o = this.element.id.slice(0, -3);
      var d = Tholos.getData(o);
      var objVal = [];
      if (d.value) {
        try {
          objVal = JSON.parse(d.value);
        } catch (e) {
          Tholos.error("TFileUpload_handleAddedFile() value conversion error", e);
        }
      }
      objVal.push(f.name);
      Tholos.setData(o, "uploadstatus", "");
      Tholos.setData(o, "value", JSON.stringify(objVal));
      $("#" + o).val(JSON.stringify(objVal));
      // TODO: battika should trigger a change here
    },
    TFileUpload_handleRemovedFile: function (f) {
      Tholos.trace("TFileUpload_handleRemovedFile()", f);
      var o = this.element.id.slice(0, -3);
      var d = Tholos.getData(o);
      var objVal = [];
      if (d.value) {
        try {
          objVal = JSON.parse(d.value);
        } catch (e) {
          Tholos.error("TFileUpload_handleRemovedFile() value conversion error", e);
        }
      }
      if ($.inArray(f.name, objVal) > -1) {
        objVal.splice($.inArray(f.name, objVal), 1);
      }
      Tholos.setData(o, "value", JSON.stringify(objVal));
      $("#" + o).val(JSON.stringify(objVal));
      // TODO: battika should trigger a change here
    },
    TFileUpload_handleSuccessMultiple: function (f, resp) {
      Tholos.trace("TFileUpload_handleSuccessMultiple()", f, resp);
      var o = this.element.id.slice(0, -3);
      if (!resp.data) {
        Tholos.setData(o, "uploadstatus", "error");
        Tholos.setData(o, "uploaderrormsg", Tholos.i18n.TFileUpload_UploadError);
      } else {
        var respData = JSON.parse(resp.data);
        if (resp.success === "OK") {
          var d = Tholos.getData(o);
          var objVal = JSON.parse(d.value);
          var replacedFiles = respData.fileSet.split(",");
          $.each(f, function (idx) {
            objVal.splice($.inArray(this.name, objVal), 1);
            objVal.push(this.name.replace(/[:;,"']/g, '\_') + ":" + replacedFiles[idx]);
          });
          Tholos.setData(o, "value", JSON.stringify(objVal));
          $("#" + o).val(JSON.stringify(objVal));
        } else {
          Tholos.setData(o, "uploadstatus", "error");
          Tholos.setData(o, "uploaderrormsg", respData.errormsg);
        }
      }
      // ha kozben hozzaadna a user file-t, akkor azt is toltse fel
      this.processQueue();
    },
    TFileUpload_handleError: function (f, resp) {
      Tholos.error("TFileUpload_handleError()", resp);
      var o = this.element.id.slice(0, -3);
      Tholos.setData(o, "uploadstatus", "error");
    },
    TFileUpload_handleQueueComplete: function () {
      Tholos.trace("TFileUpload_handleQueueComplete()");
      this.off("queuecomplete", Tholos.methods.TFileUpload_handleQueueComplete);
      this.off("successmultiple", Tholos.methods.TFileUpload_handleSuccessMultiple);
      var o = this.element.id.slice(0, -3);
      var d = Tholos.getData(o);
      this.removeAllFiles(true);
      if (d.uploadstatus !== "error") {
        Tholos.setData(o, "uploadstatus", "success");
      }
      if (Tholos.getComponentType(d.uploadsender) === "TForm") {
        Tholos.trace("TFileUpload_handleQueueComplete(): upload successful, calling TForm with original parameters");
        var callParams = JSON.parse(d.uploadsenderdata);
        Tholos.eventHandler(callParams.sender, callParams.target, "TForm", "submit", callParams.route, callParams.eventData);
      }
    },
    TLOV_refresh: function (sender, target, route, eventData) {
      Tholos.trace("TLOV_refresh()", sender, target, route, eventData);
      // TGrid filter can specify a search container in eventData.container
      // as all list filters are named 'value1' so a context is required to
      // be able to find the proper one

      var params = [];

      var urldata = [];
      // var s = Tholos.getData(sender);

      if (eventData && eventData.container) {
        var d = $(eventData.container + " > #" + target + "-props").data();
      } else {
        for (var k in eventData) {
          var item = {};
          item.name = k;
          item.value = eventData[k];
          urldata.push(item);
        }
        var d = Tholos.getData(target);
      }

      if (!d || !d.listsource) {
        Tholos.error("TLOV_refresh(): ListSource is not defined, aborting...");
        Tholos.action(false, sender, target);
        return false;
      }

      var o = $((eventData && eventData.container) ? eventData.container + " #" + d.name : "#" + d.id);

      var curSel = o.val();

      if (d.value !== undefined) {
        params.push("LOVCurrentValue=" + d.value);
      }

      if (d.master) {
        params.push(d.masterfilterfield + "=" + Tholos.eventHandler(sender, d.master, "", "getValue"));
      }

      if (d.listfilter) {
        d.listfilter.split("&").forEach(function (part) {
          var item2 = part.split("=");
          var found = false;
          if (d.dataparameters) {
            for (var k in d.dataparameters) {
              // console.log(k.toUpperCase() + ' ?==? ' + item2[0].toUpperCase());
              if (k.toUpperCase() == item2[0].toUpperCase()) {
                found = true;
                break;
              }
            }
          }
          if (eventData && !found) {
            for (var k in eventData) {
              console.log(k.toUpperCase() + ' ?==? ' + item2[0].toUpperCase());
              if (k.toUpperCase() == item2[0].toUpperCase()) {
                found = true;
                break;
              }
            }
          }
          if (!found) {
            var item = {};
            item.name = item2[0].toUpperCase();
            item.value = decodeURIComponent(item2[1]);
            urldata.push(item);
          }
        });
        // params.push(d.listfilter);
      }

      if (d.dataparameters) {
        for (var k in d.dataparameters) {
          var item = {};
          item.name = k.toUpperCase();
          item.value = d.dataparameters[k];
          urldata.push(item);
        }
      }

      /*
      if ("$prop_serversidesearch"=="true" && "$prop_searchable"=="true") {
    Tholos.methods.TLOV_refresh("$prop_id", "$prop_id", "$prop_route");
    $("#$prop_id").select2({
      minimumInputLength: $prop_MinimumSearchLength,
      ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
        url: "/SAL_INVOICE/qAllPartnersLov/",
        dataType: 'json',
        quietMillis: 250,
        data: function (params) {
            var query = {
             LOVSearchTerm: params.term,
             LOVCurrentValue: "$prop_value"
            }
          return query;
        },
        processResults: function (data) { // parse the results into the format expected by Select2.
            return { results: JSON.parse(data.data) };
        },
        cache: false
      },
    });
       */

      if (d.serversidesearch === true && d.searchable === true) {
        o.select2({
          minimumInputLength: d.minimumsearchlength,
          ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
            url: d.listsourceroute + "?" + params.join("&"),
            dataType: 'json',
            quietMillis: 250,
            data: function (params2) {
              urldata.push({name: "LOVSearchTerm", value: params2.term});
              return urldata;
            },
            processResults: function (data) { // parse the results into the format expected by Select2.
              return {results: JSON.parse(data.data)};
            },
            cache: false
          },
        });
      } else if (d.searchable === false) {
        o.select2({
          minimumResultsForSearch: Infinity
        });
      } else {
        o.select2({});
      }


      //o.append($('<option selected="selected">' + Tholos.i18n.TLOV_Loading + '</option>').val((curSel?curSel:d.value))).trigger("change");

      var refresh_needed = true;
      var new_refresh_url = d.listsourceroute + "?" + params.join("&");
      var new_refresh_data = $.param(urldata);
      if ((!d.hasOwnProperty('forcedrefresh') || (d.hasOwnProperty('forcedrefresh') && d.forcedrefresh === false)) &&
        d.hasOwnProperty('lastrefreshurl') &&
        d.hasOwnProperty('lastrefreshdata') &&
        new_refresh_url == d.lastrefreshurl &&
        new_refresh_data == d.lastrefreshdata
      ) {
        Tholos.debug('TLOV refresh NOT needed!');
        refresh_needed = false;
      } else {
        Tholos.debug('TLOV refresh check! ' + ' ::: ' + d.lastrefreshurl + ' vs ' + new_refresh_url + ' ::: ' + d.lastrefreshdata + ' vs ' + new_refresh_data);
        Tholos.setData(d.id, "lastrefreshurl", new_refresh_url);
        Tholos.setData(d.id, "lastrefreshdata", new_refresh_data);
//        console.log('TLOV refresh needed! '+d.id+' ::: '+d.lastrefreshurl+' ::: '+d.lastrefreshdata);
      }

      //TODO: battika - handle error scenario
      if (refresh_needed) {
        o.prop("disabled", true);
        $.ajaxq((d.hasOwnProperty('ajaxqueueid') ? d.ajaxqueueid : d.name),
          {
            type: "POST",
            url: d.listsourceroute + "?" + params.join("&"),
            dataType: "json",
            data: $.param(urldata),
            complete: function () {
              if (eventData && eventData.container) {
                var d2 = $(eventData.container + " > #" + target + "-props").data();
              } else {
                var d2 = Tholos.getData(target);
              }
              if (!d2.readonly === "true") {
                o.prop("disabled", false);
              }
              Tholos.trace("TLOV_refresh(): Triggering onAfterRefresh");
              o.trigger("onAfterRefresh");
            }
          }).then(function (data2) {
          if (data2.success === "OK") {
            var html = "";
            var curVal = d.value;
            if (curVal && d.multiselect) {
              curVal = curVal.split(",");
            }
            var firstId = "";
            $.each(JSON.parse(data2.data), function (index, eData) {
              if (firstId === "") firstId = eData[d.fieldid];
              html += '<option value="' + eData[d.fieldid] + '" >' + eData[d.fieldtext] + '</option>';
            });
            if (!o.prop("required")) {
              firstId = "";
              html = "<option></option>" + html;
            } else {
              /*if (curVal==="" && firstId!=="") {
                curVal=firstId;
              }*/
            }
            if (eventData && eventData.container) {
              var d2 = $(eventData.container + " > #" + target + "-props").data();
            } else {
              var d2 = Tholos.getData(target);
            }
            o.find("option").remove().end().append(html).val((curSel && curSel !== null && curSel != 'null' && curSel != 'undefined' ? curSel : curVal)).trigger("change").prop("disabled", (d2.readonly === "true" ? "disabled" : false));
            Tholos.action(true, sender, target);
          }
        });
      } else {
        Tholos.action(true, sender, target);
      }
    }
    ,
    TForm_cancel: function (sender, target, route, eventData) {
      Tholos.trace("TForm_cancel()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      // bezarni a dialogus ablakot, amiben van
      if (o.closest("div.modal").length > 0) {
        Tholos.methods.TModal_hide(sender, o.closest("div.modal").attr("id"), null, eventData);
      } else if (o.closest("div.TFormContainer").length > 0) { // ha be lett embeddelve az oldalba
        Tholos.trace("TForm_submit(): form is embedded in TFormContainer " + o.closest("div.modal").attr("id"));
        var dm = Tholos.getData(o.closest("div.TFormContainer").attr("id"));
        Tholos.action(true, sender, target);
        Tholos.methods.TContainer_clearContent(sender, dm.id, null, eventData);
      } else {
        o.trigger("onCancel", []);
        Tholos.action(false, sender, target);
      }
    }
    ,
    TForm_submit: function (sender, target, route, eventData) {
      Tholos.trace("TForm_submit()", sender, target, route, eventData);
      var d = Tholos.getData(target);
      var o = Tholos.getObject(target);
      if (!d || !o) {
        return false;
      }
      if (!document.getElementById(d.id).checkValidity()) {
        document.getElementById(d.id).classList.add('was-validated');
        return false;
      }
      ;
      var urldata = [];
      for (var k in eventData) {
        var item = {};
        item.name = k;
        item.value = eventData[k];
        urldata.push(item);
      }
      var earlyExit = !Tholos.eventHandler(target, target, "TForm", "validateClient", route, eventData);

      // collecting database related fields
      o.find(":input[type='hidden'][id$='-props']").each(function () {
          var item = {};
          var dx = $(this).data();
          if (!earlyExit && dx.componenttype === "TFileUpload") {
            if (dx.uploadstatus === "") {
              var actData = {sender: sender, target: target, route: route, eventData: eventData};
              Tholos.trace("TForm_submit(): TFileUpload to start uploading file(s), exiting current submit process");
              Tholos.eventHandler(sender, target, "TForm", "setEnabled", route, {"enabled": "false"});
              Tholos.eventHandler(target, dx.id, "TFileUpload", "upload", route, JSON.stringify(actData));
              earlyExit = true;
              return;
            } else if (dx.uploadstatus === "error") {
              Tholos.trace("TForm_submit(): Triggering onErrorAlert on TForm caused by failed upload");
              o.trigger("onErrorAlert", {errorcode: dx.uploadstatus, errormsg: dx.uploaderrormsg});
              Tholos.trace("TForm_submit(): Triggering onSubmitError on TForm caused by failed upload");
              o.trigger("onSubmitError", {errorcode: dx.uploadstatus, errormsg: dx.uploaderrormsg});
              Tholos.setData(this.id.slice(0, -6), "uploadstatus", "");
              Tholos.eventHandler(sender, target, "TForm", "setEnabled", route, {"enabled": "true"});
              Tholos.action(false, sender, target);
              earlyExit = true;
              return;
            }
          }
          // van-e mapping
          if (dx.dbparametername) {
            if (dx.dbparametername !== "-") { // ezt ne tolja vissza
              item.name = dx.dbparametername;
              item.value = Tholos.eventHandler(sender, dx.id, dx.componenttype, "getValue");
              urldata.push(item);
            }
          } else if (dx.dbfield) { // ha adatbazisbol jott, akkor a parameternev a mezonev automatikusan
            item.name = dx.dbfield;
            item.value = Tholos.eventHandler(sender, dx.id, dx.componenttype, "getValue");
            urldata.push(item);
          }
        }
      );
      if (earlyExit) {
        Tholos.trace("TForm_submit() early exiting, waiting for callback from TFileUpload or upload error");
        return;
      }
      var submitterroute = "";
      if (!d.submitter) {
        sd = Tholos.getData(target);
        if (sd.submitter) {
          submitterroute = sd.submitterroute;
        }
      } else {
        submitterroute = d.submitterroute;
      }

      o.trigger("onBeforeSubmit", urldata);

      if (submitterroute) {
        Tholos.pageLoader(true, (eventData.loader !== undefined && eventData.loader) ? true : false);
        Tholos.eventHandler(sender, target, "TForm", "setEnabled", route, {"enabled": "false"});
        $.ajaxq((d.hasOwnProperty('ajaxqueueid') ? d.ajaxqueueid : d.name),
          {
            url: submitterroute,
            type: "post",
            dataType: "json",
            data: $.param(urldata),
            contentType: "application/x-www-form-urlencoded;charset=UTF-8",
            complete: function () {
              Tholos.pageLoader(false, false);
              if (d.enableaftersubmit) {
                Tholos.eventHandler(sender, target, "TForm", "setEnabled", route, {"enabled": "true"});
              }
            },
            success: function (data) {
              if (data.success === "OK") {
                if (o.closest("div.modal").length > 0) { // ha dialogus ablakban lett megnyitva
                  Tholos.trace("TForm_submit(): form is embedded in Tmodal " + o.closest("div.modal").attr("id"));
                  var dm = Tholos.getData(o.closest("div.modal").attr("id"));
                  var om = Tholos.getObject(o.closest("div.modal").attr("id"));
                  if (dm.overrideformevents) {
                    Tholos.trace("TForm_submit(): Triggering onSuccessAlert of TModal");
                    om.trigger("onSuccessAlert", [data]);
                    Tholos.trace("TForm_submit(): Triggering onSubmitSuccess of TModal");
                    om.trigger("onSubmitSuccess", [data]);
                  } else {
                    Tholos.trace("TForm_submit(): Triggering onSuccessAlert (1) of TForm");
                    o.trigger("onSuccessAlert", [data]);
                    Tholos.trace("TForm_submit(): Triggering onSubmitSuccess (1) of TForm");
                    o.trigger("onSubmitSuccess", [data]);
                  }
                  Tholos.action(true, sender, target);
                  if (d.closemodalonsuccess) Tholos.methods.TModal_hide(sender, dm.id, null, eventData);
                } else if (o.closest("div.TFormContainer").length > 0) { // ha be lett embeddelve az oldalba
                  Tholos.trace("TForm_submit(): form is embedded in TFormContainer " + o.closest("div.modal").attr("id"));
                  var dm = Tholos.getData(o.closest("div.TFormContainer").attr("id"));
                  var om = Tholos.getObject(o.closest("div.TFormContainer").attr("id"));
                  if (dm.overrideformevents) {
                    Tholos.trace("TForm_submit(): Triggering onSuccessAlert of TFormContainer");
                    om.trigger("onSuccessAlert", [data]);
                    Tholos.trace("TForm_submit(): Triggering onSubmitSuccess of TFormContainer");
                    om.trigger("onSubmitSuccess", [data]);
                  } else {
                    Tholos.trace("TForm_submit(): Triggering onSuccessAlert (2) of TForm");
                    o.trigger("onSuccessAlert", [data]);
                    Tholos.trace("TForm_submit(): Triggering onSubmitSuccess (2) of TForm");
                    o.trigger("onSubmitSuccess", [data]);
                  }
                  Tholos.action(true, sender, target);
                  if (d.closemodalonsuccess) Tholos.methods.TFormContainer_hide(sender, dm.id, null, eventData);
                } else {
                  Tholos.trace("TForm_submit(): Triggering onSuccessAlert of TForm");
                  o.trigger("onSuccessAlert", [data]);
                  Tholos.trace("TForm_submit(): Triggering onSubmitSuccess of TForm");
                  o.trigger("onSubmitSuccess", [data]);
                  Tholos.action(true, sender, target);
                }
                if (data.callback) {
                  Tholos.processCallback(sender, data);
                }
              } else {
                Tholos.eventHandler(sender, target, "TForm", "setEnabled", route, {"enabled": "true"});
                Tholos.trace("TForm_submit(): Triggering onErrorAlert on TForm");
                o.trigger("onErrorAlert", [data]);
                Tholos.trace("TForm_submit(): Triggering onSubmitError of TForm");
                o.trigger("onSubmitError", [data]);
                Tholos.action(false, sender, target);
              }
            },
            error: function (response, textStatus, errorThrown) {
              Tholos.eventHandler(sender, target, "TForm", "setEnabled", route, {"enabled": "true"});
              Tholos.handleJSONError(response, textStatus, errorThrown);
            }
          });
      } else if (o.attr("action")) {
        Tholos.trace("TForm_submit(): Invoking regular form submit using HTML");
        o.submit();
        Tholos.action(true, sender, target);
      } else Tholos.action(false, sender, target);
    }
    ,
    TForm_validateClient: function (sender, target, route, eventData) {
      Tholos.trace("TForm_validateClient()", sender, target, route, eventData);
      var o = Tholos.getObject(target);

      if (!o[0].checkValidity()) {
        Tholos.trace("TForm_validateClient(): form is invalid, displaying native HTML5 error messages");
        $('<input type="submit">').hide().appendTo(o).click().remove();
        return false;
      } else {
        return true;
      }
    }
    ,
    TForm_validate: function (sender, target, route, eventData) {
      Tholos.trace("TForm_validate()", sender, target, route, eventData);
      var d = Tholos.getData(target);
      var sd = Tholos.getData(sender);

      if (!d || !sd || !d.validator || d.submitting) {
        Tholos.error("TForm_validate(): target or validator is undefined. Aborting...");
        return;
      }

      var o = Tholos.getObject(target);

      // disabling form while validate running
      d.submitting = true;
      Tholos.eventHandler(sender, target, "TForm", "setEnabled", route, {"enabled": "false"});

      var urldata = [];

      for (var k in eventData) {
        var item = {};
        item.name = k;
        item.value = eventData[k];
        urldata.push(item);
      }

      urldata.push({name: "SENDER_OBJECT", value: sd.name});
      // collecting database related fields
      o.find(":input[type='hidden'][id$='-props']").each(function () {
          var item = {};
          var dx = $(this).data();
          // van-e mapping
          if (dx.dbparametername) {
            if (dx.dbparametername !== "-") { // ezt ne tolja vissza
              item.name = dx.dbparametername;
              item.value = Tholos.eventHandler(sender, dx.id, dx.componenttype, "getValue");
              urldata.push(item);
            }
          } else if (dx.dbfield) { // ha adatbazisbol jott, akkor a parameternev a mezonev automatikusan
            item.name = dx.dbfield;
            item.value = Tholos.eventHandler(sender, dx.id, dx.componenttype, "getValue");
            urldata.push(item);
          }
        }
      );

      if (d.validator) {
        Tholos.pageLoader(true, false);
        $.ajaxq((d.hasOwnProperty('ajaxqueueid') ? d.ajaxqueueid : d.name),
          {
            url: d.validatorroute,
            type: "post",
            dataType: "json",
            data: $.param(urldata),
            contentType: "application/x-www-form-urlencoded;charset=UTF-8",
            complete: function () {
              Tholos.pageLoader(false, false);
              d.submitting = false;
              Tholos.eventHandler(sender, target, "TForm", "setEnabled", route, {"enabled": "true"});
            },
            success: function (data) {

              if (data.callback) {
                Tholos.processCallback(sender, data);
              }

              if (data.success !== "OK") {
                Tholos.trace("TForm_validate(): Triggering onValidateError of TForm");
                o.trigger("onValidateError", [data]);
                Tholos.action(false, sender, target);
              } else {
                Tholos.trace("TForm_validate(): Triggering onValidateSuccess of TForm");
                o.trigger("onValidateSuccess", [data]);
                Tholos.action(true, sender, target);
              }
            },
            error: function (response, textStatus, errorThrown) {
              Tholos.action(false, sender, target);
              Tholos.handleJSONError(response, textStatus, errorThrown);
            }
          });
      } else {
        d.submitting = false;
        o.attr("disabled", false);
        Tholos.action(true, sender, target);
      }
    }
    ,
    TTabPane_setLabel: function (sender, target, route, eventData) {
      Tholos.trace("TTabPane_setLabel()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      if (eventData.label) {
        $('.nav-tabs > li > a[href="#' + target + '"]').text(eventData.label);
        Tholos.setData(target, "label", eventData.label);
      }
      Tholos.action(true, sender, target);
    }
    ,
    TTabPane_activate: function (sender, target, route, eventData) {
      Tholos.trace("TTabPane_activate()", sender, target, route, eventData);
      $('.nav-tabs > li > a[href="#' + target + '"]').tab("show");
      Tholos.trace("TTabPane_activate(): Triggering onActivate");
      Tholos.action(true, sender, target);
    }
    ,
    TWorkflowStep_execute: function (sender, target, route, eventData) {
      Tholos.trace("TWorkflowStep_execute()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      Tholos.trace("TWorkflowStep_execute(): Triggering onExecute");
      o.trigger("onExecute", eventData);
    }
    ,
    TWizardStep_activate: function (sender, target, route, eventData) {
      Tholos.trace("TWizardStep_activate()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      var d = Tholos.getData(target);
      var parentWizard = $(o).closest(".wizard");
      if (parentWizard[0]) {
        parentWizard.wizard("selectedItem", {step: d.name});
      }
      Tholos.trace("TWizardStep_activate(): Triggering onShow");
      o.trigger("onShow", eventData);
      Tholos.action(true, sender, target);
    }
    ,
    TWizard_moveTo: function (sender, target, route, eventData) {
      Tholos.trace("TWizard_moveTo()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      if (eventData.step) {
        o.wizard("selectedItem", {step: eventData.step});
      }
      Tholos.action(true, sender, target);
    }
    ,
    TWizard_first: function (sender, target, route, eventData) {
      Tholos.trace("TWizard_first()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      var firstStep = o.find("li").first().data("name");
      if (firstStep.length > 0) {
        Tholos.eventHandler(sender, firstStep, "TWizardStep", "activate", route, eventData);
      }
      Tholos.action(true, sender, target);
    }
    ,
    TWizard_previous: function (sender, target, route, eventData) {
      Tholos.trace("TWizard_previous()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      o.wizard("previous");
      Tholos.action(true, sender, target);
    }
    ,
    TWizard_next: function (sender, target, route, eventData) {
      Tholos.trace("TWizard_next()", sender, target, route, eventData);
      var o = Tholos.getObject(target);
      o.wizard("next");
      Tholos.action(true, sender, target);
    }
    ,
    TConfirmDialog_show: function (sender, target, route, eventData) {
      Tholos.trace("TConfirmDialog_show()", sender, target, route, eventData);
      Tholos.findComponentId(target);
      var o = Tholos.getObject(target);
      // saving sender to able to trigger onConfirmTrue, onConfirmFalse on it
      Tholos.setData(target, "caller", sender);
      o.trigger("showdialog", eventData);
    }
    ,
    TConfirmDialog_triggerConfirmTrue: function (sender, target, route, eventData) {
      Tholos.trace("TConfirmDialog_triggerConfirmTrue()", sender, target, route, eventData);
      var d = Tholos.getData(sender);
      var o = Tholos.getObject(d.caller);
      o.trigger("onConfirmTrue", eventData);
    }
    ,
    TConfirmDialog_triggerConfirmFalse: function (sender, target, route, eventData) {
      Tholos.trace("TConfirmDialog_triggerConfirmFalse()", sender, target, route, eventData);
      var d = Tholos.getData(sender);
      var o = Tholos.getObject(d.caller);
      o.trigger("onConfirmFalse", eventData);
    }
    ,
    TMapSource_setVisible: function (sender, target, route, eventData) {
      Tholos.trace("TMapSource_setVisible()", sender, target, route, eventData);
      var d = Tholos.getData(target);
      if (d.visible !== eventData.visible) {
        Tholos.setData(target, "visible", eventData.visible);
        Tholos.eventHandler(sender, target, "TMapSource", "refresh", route, eventData);
      }
      return true;
    }
    ,
    TMapSource_refresh: function (sender, target, route, eventData) {
      Tholos.trace("TMapSource_refresh()", sender, target, route, eventData);
      var d = Tholos.getData(target);
      if (d.parentmap && d.mapsourcetype) {
        var functionCall = "TMapSource_handle" + d.mapsourcetype;
        if (typeof Tholos.methods[functionCall] === "function") {
          Tholos.methods[functionCall](d.parentmap, target);
        }
        return true;
      } else {
        return false;
      }
    }
    ,
    TMapSource_handleMarkers: async function (map, mapSource) {
      Tholos.trace("TMapSource_handleMarkers()", map, mapSource);
      const {AdvancedMarkerElement} = await google.maps.importLibrary("marker");
      var md = Tholos.getData(map);
      var msd = Tholos.getData(mapSource);
      var mo = Tholos.getObject(map);
      await window["mapInitialized_" + md.name];
      var mapObj = window["map_" + md.name];
      window.currentInfoWindow = null;

//      mapObj.hideInfoWindows();
      Tholos.trace("TMapSource_handleMarkers(): Removing all markers related to MapSource " + msd.name);
      $.each(mapObj.tholos[msd.name], function (idx, markers) {
        $.each(markers, function (idx2, marker) {
          marker.element.setMap(null);
        });
      });
      mapObj.tholos[msd.name] = [];

      if (!msd.visible) {
        return "";
      }

      Tholos.setData(mapSource, "refreshing", true);

      $.ajaxq((msd.hasOwnProperty('ajaxqueueid') ? msd.ajaxqueueid : msd.name),
        {
          type: "GET",
          dataType: "json",
          url: msd.listsourceroute + "?" + (msd.dataparameters ? Tholos.EncodeQueryData(msd.dataparameters) + "&" : "") + msd.masterfilterfield + (msd.master ? "=" + Tholos.eventHandler(mapSource, msd.master, "", "getValue") : ""),
          complete: function () {
            Tholos.setData(mapSource, "refreshing", false);
          },
          success: function (data) {

            var markers = [];
            $.each(JSON.parse(data.data), function (markerIdx, markerData) {
              if (markerData[msd.fieldlatitude] && markerData[msd.fieldlongitude]) {
                const marker = {};
                marker.tholos = {
                  map: map,
                  mapSource: mapSource,
                  mapName: md.name,
                  mapSourceName: msd.name,
                  lat: markerData[msd.fieldlatitude],
                  lng: markerData[msd.fieldlongitude],
                  listSource: msd.listsource,
                  listSourceRoute: msd.listsourceroute,
                  id: markerData[msd.fieldid]
                };
                marker.infowindow = new google.maps.InfoWindow({
                  content: markerData[msd.fieldinfowindowcontent]
                });
                if (msd.fieldinfowindowzindex) {
                  marker.infowindow.setZIndex(markerData[msd.fieldinfowindowzindex]);
                }
                if (msd.fieldicon) {
                  marker.img = document.createElement("img");
                  marker.img.src = markerData[msd.fieldicon];
                } else marker.img = null;
                marker.element = new AdvancedMarkerElement({
                  map: window["map_" + md.name],
                  position: {lat: +markerData[msd.fieldlatitude], lng: +markerData[msd.fieldlongitude]},
                  content: marker.img
                });
                if (msd.fieldtitle) {
                  marker.element.title = markerData[msd.fieldtitle];
                  const h = document.createElement("h3");
                  h.innerHTML = markerData[msd.fieldtitle];
                  marker.infowindow.setHeaderContent(h);
                  marker.tholos.title = markerData[msd.fieldtitle];
                }
                if (msd.zindex) {
                  marker.element.zIndex = msd.zindex;
                }
                marker.element.addListener('click', ((infowindow, element, tholos) => {
                  return () => {
                    if (window.currentInfoWindow != null) {
                      window.currentInfoWindow.close();
                    }
                    infowindow.open({
                      anchor: element,
                      map: window["map_" + md.name],
                    });
                    window.currentInfoWindow = infowindow;
                    Tholos.trace("TMap_refresh(): Triggering onMarkerClick", tholos);
                    mo.trigger("onMarkerClick", tholos);
                  };
                })(marker.infowindow, marker.element, marker.tholos));

                markers.push(marker);
              }
            });

            mapObj.tholos[msd.name].push(markers);

            Tholos.setData(mapSource, "refreshing", false);
            Tholos.eventHandler(mapSource, map, "TMap", "handleFitZoom", "", "");
          },
          error: function (response, textStatus, errorThrown) {
            Tholos.handleJSONError(response, textStatus, errorThrown);
          }
        });
    }
    ,
    TMapSource_handlePolylines: async function (map, mapSource) {
      Tholos.trace("TMapSource_handlePolylines()", map, mapSource);
      var md = Tholos.getData(map);
      var msd = Tholos.getData(mapSource);
      await window["mapInitialized_" + md.name];
      var mapObj = window["map_" + md.name];

      Tholos.trace("TMapSource_handlePolylines(): Removing all polylines related to MapSource " + msd.name);
      $.each(mapObj.tholos[msd.name], function (idx, oldPolyline) {
        oldPolyline.setMap(null);
      });
      mapObj.tholos[msd.name] = [];

      if (!msd.visible) {
        return "";
      }

      Tholos.setData(mapSource, "refreshing", true);

      $.ajaxq((msd.hasOwnProperty('ajaxqueueid') ? msd.ajaxqueueid : msd.name),
        {
          type: "GET",
          dataType: "json",
          url: msd.listsourceroute + "?" + (msd.dataparameters ? Tholos.EncodeQueryData(msd.dataparameters) + "&" : "") + msd.masterfilterfield + (msd.master ? "=" + Tholos.eventHandler(mapSource, msd.master, "", "getValue") : ""),
          complete: function () {
            Tholos.setData(mapSource, "refreshing", false);
          },
          success: function (data) {

            var pathCoords = [];

            $.each(JSON.parse(data.data), function (polyIdx, polyData) {
              pathCoords.push({lat: +polyData[msd.fieldlatitude], lng: +polyData[msd.fieldlongitude]});
            });

            if (!pathCoords || pathCoords.length === 0) return true;

            var polyline = new google.maps.Polyline({
              path: pathCoords,
              strokeColor: (msd.strokecolor ? msd.strokecolor : '#333580'),
              strokeOpacity: (msd.strokeopacity ? msd.strokeopacity : 0.5),
              strokeWeight: (msd.strokeweight ? msd.strokeweight : 5)
            });

            polyline.setMap(mapObj);

            // registering the new object on Map's tholos object
            mapObj.tholos[msd.name].push(polyline);

            Tholos.setData(mapSource, "refreshing", false);
            Tholos.eventHandler(mapSource, map, "TMap", "handleFitZoom", "", "");
          },
          error: function (response, textStatus, errorThrown) {
            Tholos.handleJSONError(response, textStatus, errorThrown);
          }
        });

    }
    ,
    TMapSource_handlePolygons: async function (map, mapSource) {
      Tholos.trace("TMapSource_handlePolygons()", map, mapSource);
      var md = Tholos.getData(map);
      var msd = Tholos.getData(mapSource);
      await window["mapInitialized_" + md.name];
      var mapObj = window["map_" + md.name];

      Tholos.trace("TMapSource_handlePolygons(): Removing all polygons related to MapSource " + msd.name);
      $.each(mapObj.tholos[msd.name], function (idx, oldPolygon) {
        oldPolygon.setMap(null);
      });
      mapObj.tholos[msd.name] = [];

      if (!msd.visible) {
        return "";
      }

      Tholos.setData(mapSource, "refreshing", true);

      $.ajaxq((msd.hasOwnProperty('ajaxqueueid') ? msd.ajaxqueueid : msd.name),
        {
          type: "GET",
          dataType: "json",
          url: msd.listsourceroute + "?" + (msd.dataparameters ? Tholos.EncodeQueryData(msd.dataparameters) + "&" : "") + msd.masterfilterfield + (msd.master ? "=" + Tholos.eventHandler(mapSource, msd.master, "", "getValue") : ""),
          complete: function () {
            Tholos.setData(mapSource, "refreshing", false);
          },
          success: function (data) {
            $.each(JSON.parse(data.data), function (polyIdx, polyData) {

              var path = JSON.parse(polyData[msd.fieldcoords]);

              if (!path || path.length === 0) return true;

              const pathCoords = JSON.parse('[]');
              $.each(path, function (i, e) {
                if (msd.swapareacoords) {
                  pathCoords.push({lat: e[1], lng: e[0]});
                } else {
                  pathCoords.push({lat: e[0], lng: e[1]});
                }
              });

              const polygon = new google.maps.Polygon({
                paths: pathCoords,
                strokeColor: (msd.strokecolor ? msd.strokecolor : '#97ADBA'),
                strokeOpacity: (msd.strokeopacity ? msd.strokeopacity : 1),
                strokeWeight: (msd.strokeweight ? msd.strokeweight : 3),
                fillColor: (msd.fillcolor ? msd.fillcolor : '#BBD8E9'),
                fillOpacity: (msd.fillopacity ? msd.fillopacity : 0.6),
              });

              polygon.setMap(mapObj);

              // registering the new object on Map's tholos object
              mapObj.tholos[msd.name].push(polygon);

              Tholos.setData(mapSource, "refreshing", false);
              Tholos.eventHandler(mapSource, map, "TMap", "handleFitZoom", "", "");
            });
          },
          error: function (response, textStatus, errorThrown) {
            Tholos.handleJSONError(response, textStatus, errorThrown);
          }
        });
    }
    ,
    TMap_refresh: async function (sender, target, route, eventData) {
      Tholos.trace("TMap_refresh()", sender, target, route, eventData);
      var d = Tholos.getData(target);
      var ob = Tholos.getObject(target);
      await window["mapInitialized_" + d.name];
      var o = window["map_" + d.name];

      if (o.tholos) {
        $.each(o.tholos, function (mapSourceName, obj) {
          var msrc = Tholos.findComponentId(mapSourceName);
          if (msrc) {
            var dmsrc = Tholos.getData(msrc);
            var functionCall = "TMapSource_handle" + dmsrc.mapsourcetype;
            if (typeof Tholos.methods[functionCall] === "function") {
              Tholos.methods[functionCall](target, msrc);
            }
          }
        });
//        Tholos.eventHandler(sender, target, "TMap", "handleFitZoom", route, eventData);
      }
    }
    ,
    TMap_handleFitZoom: function (sender, target, route, eventData) {
      Tholos.trace("TMap_handleFitZoom()", sender, target, route, eventData);
      var d = Tholos.getData(target);
      if (d.autofitzoom) {
        Tholos.eventHandler(sender, target, "TMap", "fitZoom", route, eventData);
      }
    }
    ,
    TMap_fitZoom: async function (sender, target, route, eventData) {
      Tholos.trace("TMap_fitZoom()", sender, target, route, eventData);
      var d = Tholos.getData(target);
      await window["mapInitialized_" + d.name];
      var o = window["map_" + d.name];
      var refreshing = false;
      if (o.tholos) {
        $.each(o.tholos, function (mapSourceName, obj) {
          var msrc = Tholos.findComponentId(mapSourceName);
          if (msrc) {
            var dmsrc = Tholos.getData(msrc);
            refreshing = refreshing || dmsrc.refreshing;
          }
        });

        if (refreshing) {
          Tholos.trace("TMap_fitZoom() - Some of the map sources are still loading. Cancelling TMap_fitZoom()");
          return false;
        }
      } else {
        return false;
      }
      Tholos.trace("TMap_fitZoom() - all map sources have finished loading. Running fitZoom()");

      const {LatLngBounds} = await google.maps.importLibrary("core");
      const mapbounds = new google.maps.LatLngBounds();
      let boundExists = false;

      if (o.tholos) {
        $.each(o.tholos, function (mapSourceName, markers) {
          var msrc = Tholos.findComponentId(mapSourceName);
          if (msrc) {
            var dmsrc = Tholos.getData(msrc);
            if (dmsrc.mapsourcetype == 'Markers') {
              $.each(markers[0], function (idx2, marker) {
                mapbounds.extend(marker.element.position);
                boundExists = true;
              });
            }
          }
        });
      }

      if (boundExists) {
        o.setCenter(mapbounds.getCenter());
        o.fitBounds(mapbounds);
      }

      return true;
    }
    ,
    TMap_setCenter: async function (sender, target, route, eventData) {
      Tholos.trace("TMap_setCenter()", sender, target, route, eventData);
      var d = Tholos.getData(target);
      await window["mapInitialized_" + d.name];
      var o = window["map_" + d.name];
      var ed = {};
      $.each(eventData, function (n, v) {
        ed[n] = parseFloat(v);
      });
      try {
        o.setCenter(ed);
        return true;
      } catch (e) {
        return false;
      }
    }
    ,
    TTimer_activate: function (sender, target, route, eventData) {
      // Tholos.trace("TTimer_activate()",sender,target,route,eventData);
      if (eventData.loader !== undefined && eventData.loader) setTimeout(function () {
        Tholos.pageLoader(true, true, true);
      }, 500);
      Tholos.findComponentId(target);
      var o = Tholos.getObject(target);
      o.trigger("activate", eventData);
    }
    ,
    TTimer_execute: function (sender, target, route, eventData) {
      // Tholos.trace("TTimer_execute()",sender,target,route,eventData);
      Tholos.findComponentId(target);
      var o = Tholos.getObject(target);
      o.trigger("onExecute", eventData);
    }
    ,

    TDiagramEditor_refresh: function (sender, target, route, eventData) {
      Tholos.trace("TDiagramEditor_refresh", sender, target, route, eventData);
      var d = Tholos.getData(target);
      var ob = Tholos.getObject(target);
      var inst = window["jsPlumb_" + d.name];
      var elPrefix = d.name + "-";
      var filterString = "";
      $('#' + target + '-refresh').addClass("fa-spin");

      if (d.master && d.masterfilterfield) {
        filterString = d.masterfilterfield + "=" + Tholos.eventHandler(sender, d.master, "", "getValue");
      }

      // Get Endpoints
      $.ajax({
        type: "GET",
        dataType: "json",
        url: d.listsourcenodesroute + "?" + filterString,

        complete: function () {
          //
        },
        success: function (data) {
          console.log('received data', data);
          try {

            /*
                             data = "[" +
                                 "{\"id\":\"1001\",\"text\":\"Mosatas\",\"position_x\":\"100\",\"position_y\":\"350\",\"class\":\"\"},\n" +
                                 "{\"id\":\"1002\",\"text\":\"Toltes\",\"position_x\":\"500\",\"position_y\":\"500\",\"class\":\"\"},\n" +
                                 "{\"id\":\"1003\",\"text\":\"Transzfer\",\"position_x\":\"200\",\"position_y\":\"500\",\"class\":\"\"},\n" +
                                 "{\"id\":\"1004\",\"text\":\"Elhozatal\",\"position_x\":\"500\",\"position_y\":\"200\",\"class\":\"\"}\n" +
                                 "]";
            */
            var deNodes = JSON.parse(data['data']);

            // Add new nodes
            for (var i = 0; i < deNodes.length; i++) {
              if (deNodes[i].hasOwnProperty('id') && deNodes[i].id) {
                if (!$("#" + elPrefix + deNodes[i].id).length) {
                  Tholos.trace('TDiagramEditor_refresh(): Creating new node ' + deNodes[i].id);
                  inst.tholos.newNode(deNodes[i].id, deNodes[i].text, deNodes[i].position_x, deNodes[i].position_y, deNodes[i].class);
                } else {
                  var repaintNeeded = false;
                  // node already exits check for changed properties
                  var node = $("#" + elPrefix + deNodes[i].id);
                  if (node.position().left != deNodes[i].position_x) {
                    node.css({left: deNodes[i].position_x + "px"});
                    repaintNeeded = true;
                  }
                  if (node.position().top != deNodes[i].position_y) {
                    node.css({top: deNodes[i].position_y + "px"});
                    repaintNeeded = true;
                  }


                  if (deNodes[i].text + inst.tholos.endpointMarkup != node.html()) {
                    node.html(deNodes[i].text + inst.tholos.endpointMarkup);
                    repaintNeeded = true;
                  }

                  if (repaintNeeded) {
                    inst.revalidate(elPrefix + deNodes[i].id);
                  }
                }
              }
            }

            // Remove deleted nodes
            var validNodes = [];
            for (var i = 0; i < deNodes.length; i++) {
              if ((deNodes[i].hasOwnProperty('id') && deNodes[i].id)) {
                validNodes.push(elPrefix + deNodes[i].id);
              }
            }
            $('#' + d.id + '>.tholos-diagrameditor-node').each(
              function () {
                if (validNodes.indexOf($(this).attr('id')) == -1) {
                  Tholos.trace('TDiagramEditor_refresh(): Removing node ' + $(this).attr('id'));
                  inst.remove($(this).attr('id'));
                }
              }
            );

            // Get connectors
            $.ajax({
              type: "GET",
              dataType: "json",
              url: d.listsourceconnectorsroute + "?" + filterString,
              complete: function () {
                $('#' + target + '-refresh').removeClass("fa-spin");
              },
              success: function (data) {
                try {
                  /*
                                               data = "[" +
                                                   "{\"source_id\":\"1001\",\"target_id\":\"1002\",\"type\":\"basic\"},\n" +
                                                   "{\"source_id\":\"1003\",\"target_id\":\"1004\",\"type\":\"basic\"},\n" +
                                                   "{\"source_id\":\"1001\",\"target_id\":\"3456\",\"type\":\"basic\"},\n" +
                                                   "{\"source_id\":\"1003\",\"target_id\":\"3456\",\"type\":\"basic\"}\n" +
                                                   "]";
                  */
                  var deConnectors = JSON.parse(data['data']);
                  for (var j = 0; j < deConnectors.length; j++) {
                    if (deConnectors[j].hasOwnProperty('source_id') && deConnectors[j].hasOwnProperty('target_id')) {
                      var connected = inst.getConnections({source: elPrefix + deConnectors[j].source_id});
                      var connectionExists = false;
                      $.each(connected, function (e, s) {
                        if (elPrefix + deConnectors[j].target_id == s.targetId) {
                          connectionExists = true;
                          return false;
                        }
                      });
                      if (!connectionExists) {
                        if (validNodes.indexOf(elPrefix + deConnectors[j].target_id) != -1) {
                          inst.tholos.connect(elPrefix + deConnectors[j].source_id, elPrefix + deConnectors[j].target_id, deConnectors[j].type);
                          inst.revalidate(elPrefix + deConnectors[j].source_id);
                          inst.revalidate(elPrefix + deConnectors[j].target_id);
                        } else {
                          Tholos.trace('TDiagramEditor_refresh(): Unexpected error: target does not exist: ' + elPrefix + deConnectors[j].target_id);
                        }
                      }
                    }
                  }

                  // Remove unused connections
                  var connected = inst.getConnections();

                  $.each(connected, function (e, s) {
                    var connectionExists = false;
                    for (var j = 0; j < deConnectors.length; j++) {
                      if (deConnectors[j].hasOwnProperty('source_id') &&
                        deConnectors[j].hasOwnProperty('target_id') &&
                        (elPrefix + deConnectors[j].source_id) == s.sourceId &&
                        (elPrefix + deConnectors[j].target_id) == s.targetId) {
                        connectionExists = true;
                        break;
                      }
                    }
                    if (!connectionExists) {
                      Tholos.trace('TDiagramEditor_refresh(): Removing connection ' + s.sourceId + '->' + s.targetId);
                      inst.deleteConnection(s, {fireEvent: false, doNotFireEvent: true});
                    }
                  });

                } catch (e) {
                  $('#' + target + '-refresh').removeClass("fa-spin");
                  console.log('ERROR', e);
                  // TODO: handle JSON parse error
                }
              },
              error: function (response, textStatus, errorThrown) {
                $('#' + target + '-refresh').removeClass("fa-spin");
                Tholos.handleJSONError(response, textStatus, errorThrown);
              }
            });
          } catch (e) {
            $('#' + target + '-refresh').removeClass("fa-spin");
            console.log(e);
            // TODO: handle JSON parse error
          }
        },
        error: function (response, textStatus, errorThrown) {
          $('#' + target + '-refresh').removeClass("fa-spin");
          Tholos.handleJSONError(response, textStatus, errorThrown);
        }
      });
    }
  },

  alert: function (sender, text) {
    alert(sender + " said: " + text);
  },

  isHTMLDocument: function (str) {
    if (str.toUpperCase().indexOf("<HTML") === -1) {
      return false;
    }
    let doc = new DOMParser().parseFromString(str, "text/html");
    return Array.from(doc.body.childNodes).some(node => node.nodeType === 1);
  },

  handleJSONError: function (response, textStatus, errorThrown) {
    Tholos.error("handleJSONError()", arguments);
    toastr.options = {
      positionClass: 'toastr-bottom-right',
      extendedTimeOut: 0, //1000;
      timeOut: 5000,
      closeButton: true
    };
    let errorText = '';
    if (response.status === 401) {
      errorText = Tholos.i18n.TApplication_HTTP_401;
      let redirect = response.getResponseHeader("x-redirect-location");
      if (redirect !== '') {
        console.log('AJAX X-Redirect');
        document.location = redirect;
      }
    } else if (response.status === 403) {
      errorText = Tholos.i18n.TApplication_HTTP_403;
      let info = response.getResponseHeader("x-tholos-security-info");
      if (info !== '') {
        errorText = errorText + '( ' + info + ')';
      }
    } else if (response.status === 500) {
      errorText = Tholos.i18n.TApplication_HTTP_500;
    } else if (response.status === 302) {
      errorText = Tholos.i18n.TApplication_HTTP_302;
      let redirect = response.getResponseHeader("location");
      if (redirect !== '') {
        console.log('AJAX Redirect');
        document.location = redirect;
      }
    } else if (response.status === 0) {
      errorText = Tholos.i18n.TApplication_HTTP_0;
    } else if (response.status === 200) {
      if (response.responseText) {
        let doc = response.responseText;
        if (Tholos.isHTMLDocument(doc)) {
          console.log('AJAX Page overwrite with response HTML');
          document.open();
          document.write(doc);
          document.close();
        } else {
          errorText = Tholos.i18n.TApplication_HTTP_200_1;
        }
      } else {
        errorText = Tholos.i18n.TApplication_HTTP_200_2;
      }
    }
    if (errorText) {
      toastr.error(errorText);
    }
  },

  getComponentName: function (componentId) {
    return componentId.substring(12);
  },

  getComponentType: function (componentId) {
    return $("#" + componentId + "-props").data("componenttype");
  },

  getData: function (componentId) {
    return $("#" + componentId + "-props").data();
  },

  setData: function (componentId, property, eventData) {
    $("#" + componentId + "-props").data(property, eventData);
  },

  getObject: function (componentId) {
    return $("#" + componentId);
  },

  findComponentId: function (componentId) {
    if ($("#" + componentId).length) {
      return componentId;
    } else {
      Tholos.trace("findComponentID", componentId);
      if (componentId.length > 13 && componentId.charAt(11) == "_" && componentId.substr(0, 3) == "TRI") {
        var cID = componentId.substr(12);
      } else {
        var cID = componentId;
      }
      var searchTerm = " :input[type='hidden'][id$='-props'][data-name='" + cID + "']";
      var cnt = $(searchTerm).length;
      if (cnt == 0) {
        searchTerm = ":regex(id,^TRI.{8}_" + cID + ")";
        cnt = $(searchTerm).length;
      }
      if (cnt == 0) {
        Tholos.debug("findComponentId(): No component found with name " + cID);
        return "";
      } else {
        if (cnt == 1) {
          var id = $(searchTerm).attr("id").replace("-props", "");
          Tholos.trace("findComponentID(): component found: " + id);
          return id;
        } else {
          Tholos.error("findComponentId(): Multiple components found with name " + cID);
          return "";
        }
      }
    }
  },

  EncodeQueryData: function (data) {
    var ret = [];
    for (var d in data)
      ret.push(encodeURIComponent(d) + "=" + encodeURIComponent(data[d]));
    return ret.join("&");
  },

  pageLoader: function (state, immediate, force) {
    if (force === undefined) force = false;
    if (state === true) {
      if (force) {
        // $(".loading-container").removeClass("loading-inactive");
        KTApp.showPageLoading();
      } else
        tholosPageLoaderTimer = setTimeout(function () {
            if ($.active > 0) {
              KTApp.showPageLoading();
            }
          }, (immediate ? 0 : 3000)
        );
    } else {
      if (typeof tholosPageLoaderTimer !== "undefined") clearTimeout(tholosPageLoaderTimer);
      KTApp.hidePageLoading();
    }
  },

  /*
    Processes callback structure typically sent by the database backend after validate event
    Syntax: JSON object with key-value pairs. Value contains additional JSON objects
    Key syntax:
      "FIELD" - database field name, all controls bound to this field will process all control parameters defined in value
      "#COMP" - direct assignment, the specified component will receive all control parameters defined in value. Component will
                be searched inside the sender object first (usually a form) and then search will be broadened to the entire
                HTML document. When multiple or zero hits found no further processing takes place and error message will be logged.

    Value syntax:
      {"property"   : "property_value"} - property will receive the specified property value
      {"function()" : "parameters"} - specified component method will be called with the specified parameters

   Supported properties: enabled, visible, required, readonly, value, label, errormsg

   It is possible to stack multiple properties and/or functions and they will be executed in the specified order.

    Sample callback:

   {
   "CODE" : {"required" : "true", "label" : "MyCode", "errormsg" : "Code not specified"},
   "ENCODE" : {"value" : "Y"},
   "#edDESCRIPTION" : {"enabled" : "false"},
   "#lovDATA_TYPE" : {"refresh()" : ""},
   "VALUE" : {"setReadOnly()" : {"readonly" : "true"}}
   }

    data.callback = '{' +
    '"CODE" : {"required" : "true", "label" : "Kóder", "errormsg" : "Kód nincs megadva"}, ' +
    '"ENCODE" : {"value" : "Y"},' +
    '"#edDESCRIPTION" : {"enabled" : "false"},' +
    '"#lovDATA_TYPE" : {"refresh()" : ""},' +
    '"VALUE" : {"setReadOnly()" : {"readonly" : "true"}}' +
    '}';
  */

  processCallback: function (sender, data) {
    Tholos.trace("processCallback()", arguments);
    var compFinder = function (prefix, controlObject, controlParam) {
      var matchingComps = $(prefix + " :input[type='hidden'][id$='-props'][data-name='" + controlObject.slice(1) + "']");
      if (matchingComps.length === 1) {
        // one exact match, call event handler
        Tholos.eventHandler(sender, matchingComps.data("id"), "", "parseControlParameters", null, controlParam);
      }
      return matchingComps.length;
    };

    try {
      if (data.callback) {
        var callback = JSON.parse(data.callback);
        $.each(callback, function (controlObject, controlParam) {
          if (controlObject.charAt(0) === "#") {
            Tholos.debug("processCallback(): looking for " + controlObject);
            //component assignment (#)
            //check if we find the referenced component inside sender object (usually a form)
            var matches = compFinder("#" + sender, controlObject, controlParam);
            if (matches === 0) {
              // when no hits, broaden search
              matches = compFinder("", controlObject, controlParam);
              if (matches === 0) {
                // broadened search fails with zero result
                Tholos.debug("processCallback(): referenced component " + controlObject + " was not found");
              } else {
                // broadened search yields multiple results
                if (matches > 1) {
                  Tholos.debug("processCallback(): referenced component " + controlObject + " was found multiple times out of context");
                }
              }
            } else {
              // search inside sender object yields multiple results
              if (matches > 1) {
                Tholos.debug("processCallback(): referenced component " + controlObject + " was found multiple times within the sender object " + sender);
              }
            }
          } else {
            // dbfield assignment
            $(":input[type='hidden'][id$='-props'][data-dbfield='" + controlObject + "']").each(function () {
              Tholos.eventHandler(sender, $(this).data("id"), $(this).data("componenttype"), "parseControlParameters", null, controlParam);
            });
          }
        });
      }
    } catch (e) {
      Tholos.error("processCallback(): Invalid JSON callback structure specified or other exception occured", data.callback, e);
    }
  },

  _eventHandler: function (sender, target, targetType, targetMethod, route, eventData) {
    var functionCall = targetType + "_" + targetMethod;

    if (typeof this.methods[functionCall] === "function") {
      return this.methods[functionCall](sender, target, route, eventData);
    } else {
      var parentTargetType = TholosComponentTypes[targetType];
      if (parentTargetType) {
        return this._eventHandler(sender, target, parentTargetType, targetMethod, route, eventData);
      }
    }
  },

  eventHandler: function (sender, target, targetType, targetMethod, route, eventData, userData) {
    Tholos.trace("eventHandler()", sender, target, targetType, targetMethod, route, eventData, userData);
    var sd = Tholos.getData(sender);

    if (!eventData) {
      eventData = {};
    }

    if (!userData) {
      userData = {};
    }

    // Merging local and global eventData variables
    if (sd && sd.dataparameters) {
      for (var k in sd.dataparameters) {
        eventData[k] = sd.dataparameters[k];
      }
    }

    $.each(userData, function (udIdx, udValue) {
      udValue = udValue.toString();
      if (udValue.charAt(0) === "#") {
        if (udValue.indexOf(".") === -1) {
          Tholos.debug("eventHandler(): parameter starting with # was passed but no . was found");
        } else {
          var sVal = udValue.split(".");
          var pObj_name = sVal[0].slice(1);
          if (pObj_name == 'this') pObj_name = sender;
          var pObj = Tholos.findComponentId(pObj_name);
          if (pObj === "") {
            Tholos.debug("eventHandler(): cannot evaluate expression as parameter object cannot be found (" + pObj_name + ")");
          } else {
            if (udValue.slice(-2) !== "()") {
              var dObj = Tholos.getData(pObj);
              udValue = dObj[sVal[1]];
            } else {
              udValue = Tholos.eventHandler(sender, pObj, "", sVal[1].slice(0, -2));
            }
            Tholos.trace("eventHandler(): evaluated value of " + udIdx + " parameter is " + udValue);
          }
        }
      }
      eventData[udIdx] = udValue;
    });

    if (!targetType) {
      targetType = this.getComponentType(target);
    }

    var renderedTarget = Tholos.findComponentId(target); // ha a target mas renderid alatt van, ha nem talal az eredetivel adja tovabb
    if (!targetType) {
      targetType = this.getComponentType(renderedTarget);
    }
    return this._eventHandler(sender, (renderedTarget == "" ? target : renderedTarget), targetType, targetMethod, route, eventData);
  },

  getSessionStorageID: function () {
    if (typeof (Storage) !== "undefined") {
      if (sessionStorage.getItem("sessionStorageID")) {
        return sessionStorage.getItem("sessionStorageID");
      } else {
        var c = 1;
        var d = new Date(),
          m = d.getMilliseconds() + "",
          u = ++d + m + (++c === 10000 ? (c = 1) : c);

        sessionStorage.setItem("sessionStorageID", u);
        return sessionStorage.getItem("sessionStorageID");
      }
    } else {
      return "";
    }
  },

  action: function (result, sender, target) {
    if (Tholos.getComponentType(sender) == "TWorkflowStep") {
      var o = this.getObject(sender);
      if (result) {
        Tholos.trace("Triggering onSuccess", arguments);
        o.trigger("onSuccess", null);
      } else {
        Tholos.trace("Triggering onError", arguments);
        o.trigger("onError", null);
      }
    }
  },

  setClickType: function (clickType) {
    if (clickType == 1) TholosLastClickType = "left";
    else if (clickType == 2) TholosLastClickType = "middle";
    else TholosLastClickType = "right";
  },

  trace: function () {
    if (TholosJSDebugLevel == "trace") {
      console.log(arguments);
    }
  },

  debug: function () {
    if (TholosJSDebugLevel == "trace" || TholosJSDebugLevel == "debug") {
      console.log(arguments);
    }
  },

  error: function () {
    console.error(arguments);
  },

  showHelp: function (id) {
    window.open(TholosHelpFile + '#' + id, 'help');
    // valami
  },

  generateDocumentTitle: function () {
    let title = '';
    $('tholosDocumentTitle').each(function (index) {
      title = (title === '' ? '' : title + ' | ') + $(this).html();
    });
    if (title !== '') document.title = title;
  }
};

function gui_ajax_spinner() {
  if ($('#gui_ajax_spinner').length) {
    if ($.active > 0) {
      $('#gui_ajax_spinner').show();
    } else {
      $('#gui_ajax_spinner').hide();
      KTApp.hidePageLoading();
    }
    setTimeout(function () {
      gui_ajax_spinner();
    }, 500);
  }
}

// Ajax redirect header handling
$(document).ready(function () {

  $(document).ajaxSuccess(function (event, request, settings) {
    if (request.getResponseHeader('X-Tholos-Redirect') && request.getResponseHeader('X-Tholos-Redirect').length > 0) {
      window.location = request.getResponseHeader('X-Tholos-Redirect');
    } else if (request.getResponseHeader('Location') && request.getResponseHeader('Location').length > 0) {
      window.location = request.getResponseHeader('Location');
    }
  });

  $(document).ajaxError(function (event, request, settings) {
    if (request.getResponseHeader('X-Tholos-Redirect') && request.getResponseHeader('X-Tholos-Redirect').length > 0) {
      window.location = request.getResponseHeader('X-Tholos-Redirect');
    }
  });

  setTimeout(function () {
    gui_ajax_spinner();
  }, 500);

});
