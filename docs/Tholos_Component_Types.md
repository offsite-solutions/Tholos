# Tholos :: Component Type documentation

#### Component Type structure
```
TComponent
     TAction
     TApplication
     TConfirmButton
     TConfirmDialog
     TContainer
         TCell
         TColumn
         TForm
         TFormContainer
         TGridRowActions
         TModal
         TPage
             TPDFPage
         TTabPane
         TTabs
         TWidget
         TWizard
         TWizardStep
     TControl
         TButton
             TButtonDropdown
         TFormControl
             TCheckbox
             TDateTimePicker
             TEdit
             TFileUpload
             THTMLEdit
             TLabel
             TLOV
             TRadio
             TStatic
             TText
         TGrid
         THidden
         TImage
         TLink
         TMap
     TDataProvider
         TExternalDataProvider
         TFileProcessor
         TJSONDataProvider
         TQuery
         TQueryGroup
         TStoredProcedure
             TAPIPost
     TDataProxy
     TDBField
         TJSONField
     TDBParam
     TDiagramEditor
     TDocumentTitle
     TDPOpen
     TGridControls
         TGridColumn
         TGridFilter
         TGridParameter
         TGridRow
     TIterator
     TJSLib
     TLinkedComponent
     TMapSource
     TPAdES
     TParameter
         TAPIParameter
         TDataParameter
         TDataProxyParameter
         TGlobalParameter
         TLOVParameter
     TPartial
     TQueryFilter
     TQueryFilterGroup
     TRoleManager
     TRoute
     TTemplate
     TTimer
     TWorkflow
     TWorkflowStep
```

#### Application structure
```
TApplication
     TDataProxy
         TDataProxyParameter
     TPAdES
     TPage
     TPDFPage
     TRoleManager
     TRoute
         TAction
             TConfirmDialog
                 TConfirmButton
             TFileProcessor
             TModal
             TTemplate
         TAPIPost
         TExternalDataProvider
             TAPIParameter
         TQuery
         TQueryGroup
         TStoredProcedure
```

#### Visual components
```
TComponent
     TCell
     TContainer
         TButton
         TButtonDropdown
         TCheckbox
         TColumn
         TDateTimePicker
         TDiagramEditor
         TEdit
         TFileUpload
         TForm
         TGrid
             TGridFilter
             TGridParameter
             TGridRow
         THidden
         THTMLEdit
         TImage
         TLink
         TLOV
             TLOVParameter
         TMap
             TMapSource
         TRadio
         TStatic
         TTabs
             TTabPane
         TText
         TWidget
         TWizard
             TWizardStep
     TDataParameter
     TDocumentTitle
     TDPOpen
     TFormContainer
     TGlobalParameter
     TGridColumn
     TGridRowActions
     TIterator
     TJSLib
     TLabel
     TLinkedComponent
     TPartial
     TQueryFilter
     TQueryFilterGroup
     TTimer
     TWorkflow
         TWorkflowStep
```

## TComponent

*TComponent*

**Parent must be: only inherited types can be placed**

TComponent is the father of all Tholos components. Super minimalistic, only defines few basic properties, methods and events.

### TComponent properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| DevNote | TEXT | No | No | No |  |  | Developer Note |
| FunctionCode | STRING | No | No | No |  |  | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Name | STRING | Yes | No | Yes |  |  | Generic name |
| NameSuffix | STRING | No | No | No |  |  | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |

### TComponent events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP |  |  |
| onAfterRender | PHP |  |  |
| onBeforeCreate | PHP |  |  |
| onBeforeRender | PHP |  |  |

### TComponent methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() |  | Sets data-dataparameters values. If previous values exist they will be merged. |

## TAction

*Inheritance: TComponent / TAction*

**Parent must be: TRoute**

TAction

### TAction properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FunctionCode | STRING | Yes | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| HTTPMethod | STRING | No | No | Yes |  |  | HTTP method that the action will handle List of possible valuesGET HEAD POST PUT DELETE TRACE OPTIONS CONNECT PATCH |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | No |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| Page | TPage | No | No | No |  |  |  |
| ResponseType | STRING | Yes | No | Yes | HTML |  | Type of the generated response. HTML will render a full HTML5-compliant page while HTMLSnippet will only render a partial useful for AJAX page generators. JSON and PLAINTEXT are self-explanatory. List of possible valuesHTML JSON JSONDATA PLAINTEXT XML NONE |

### TAction events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TAction methods

| Name | Ancestor | Description |
| --- | --- | --- |
| navigate() |  | Navigate to this action |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TApplication

*Inheritance: TComponent / TApplication*

**Parent must be: must be a root element of application structure**

TApplication

### TApplication properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |

### TApplication events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |
| onError | PHP |  |  |

### TApplication methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TConfirmButton

*Inheritance: TComponent / TConfirmButton*

**Parent must be: TConfirmDialog**

TConfirmButton

### TConfirmButton properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Class | STRING | No | No | No |  |  | HTML class. Used for injecting extra classes into the control. |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Label | STRING | Yes | No | Yes | [:test,Y:] |  | Label text of the control |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| Value | STRING | No | No | Yes |  |  | Text value of the control |

### TConfirmButton events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onClick | GUI |  | onClick event |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TConfirmButton methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TConfirmDialog

*Inheritance: TComponent / TConfirmDialog*

**Parent must be: TAction**

TConfirmDialog

### TConfirmDialog properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Backdrop | BOOLEAN | Yes | No | Yes | true |  |  |
| ConfirmType | STRING | Yes | No | Yes | YesNo |  | List of possible valuesYesNo OKCancel OK YesNoCancel Custom |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| Enabled | BOOLEAN | Yes | No | Yes | true |  | When true (default), control is enabled. Otherwise control is disabled. |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Label | STRING | Yes | No | Yes |  |  | Label text of the control |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| Title | STRING | Yes | No | Yes |  |  |  |
| Value | STRING | No | Yes | Yes |  |  | Text value of the control |

### TConfirmDialog events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onClickCANCEL | GUI |  |  |
| onClickNO | GUI |  |  |
| onClickOK | GUI |  |  |
| onClickYES | GUI |  |  |
| onDisabled | GUI |  |  |
| onHide | GUI |  | Fired when component transitions from shown to hidden |
| onShow | GUI |  | Fired when component transitions from hidden to shown |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TConfirmDialog methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
| show() |  |  |

## TContainer

*Inheritance: TComponent / TContainer*

**Parent must be: TComponent**

Container element

### TContainer properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| AJAXQueueID | STRING | Yes | No | Yes | @this.Name |  |  |
| Attributes | TEXT | No | No | No |  |  | HTML Special attributes |
| Class | STRING | No | No | No |  |  | HTML class. Used for injecting extra classes into the control. |
| ContainerType | STRING | Yes | No | No | div |  | List of possible valuesdiv li ul ol tr td tbody thead tfoot table span nav section header footer aside |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| DoNotAddCloseTag | BOOLEAN | Yes | No | No | false |  | Do not add HTML close tag to container |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Generate | BOOLEAN | Yes | No | No | true |  |  |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | No |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| SourceURL | STRING | No | No | Yes |  |  | Source URL pointing to the requested resource |
| Style | STRING | No | No | No |  |  | Style information |
| Visible | BOOLEAN | Yes | No | Yes | true |  | When true, control is visible, hidden otherwise |

### TContainer events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onClick | GUI |  | onClick event |
| onContentCleared | GUI |  |  |
| onContentLoaded | GUI |  | Fired after loadContent successfully executed |
| onHide | GUI |  | Fired when component transitions from shown to hidden |
| onReady | GUI |  | triggers automatically on page generated |
| onShow | GUI |  | Fired when component transitions from hidden to shown |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TContainer methods

| Name | Ancestor | Description |
| --- | --- | --- |
| clearContent() |  | Clears its content |
| hide() |  |  |
| loadContent() |  | Loads a content from extrenal URL received in sourceURL parameter |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
| setEnabled() |  | Set component enabled/disabled |
| setVisible() |  |  |
| show() |  |  |

## TCell

*Inheritance: TComponent / TContainer / TCell*

**Parent must be: TComponent**

TCell

### TCell properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| AJAXQueueID | STRING | Yes | No | Yes | @this.Name | TContainer |  |
| Attributes | TEXT | No | No | No |  | TContainer | HTML Special attributes |
| Class | STRING | No | No | No |  | TContainer | HTML class. Used for injecting extra classes into the control. |
| ContainerType | STRING | Yes | No | No | div | TContainer | List of possible valuesdiv li ul ol tr td tbody thead tfoot table span nav section header footer aside |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| DoNotAddCloseTag | BOOLEAN | Yes | No | No | false | TContainer | Do not add HTML close tag to container |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Generate | BOOLEAN | Yes | No | No | true | TContainer |  |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | No |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| Position | STRING | Yes | No | No | absolute |  | List of possible valuesstatic relative fixed absolute sticky |
| PositionBottom | STRING | No | No | No |  |  |  |
| PositionHeight | STRING | No | No | No | auto |  |  |
| PositionLeft | STRING | No | No | No |  |  |  |
| PositionRight | STRING | No | No | No |  |  |  |
| PositionTop | STRING | No | No | No |  |  |  |
| PositionWidth | STRING | No | No | No | auto |  |  |
| SourceURL | STRING | No | No | Yes |  | TContainer | Source URL pointing to the requested resource |
| Style | STRING | No | No | No |  | TContainer | Style information |
| Visible | BOOLEAN | Yes | No | Yes | true | TContainer | When true, control is visible, hidden otherwise |

### TCell events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onClick | GUI | TContainer | onClick event |
| onContentCleared | GUI | TContainer |  |
| onContentLoaded | GUI | TContainer | Fired after loadContent successfully executed |
| onHide | GUI | TContainer | Fired when component transitions from shown to hidden |
| onReady | GUI | TContainer | triggers automatically on page generated |
| onShow | GUI | TContainer | Fired when component transitions from hidden to shown |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TCell methods

| Name | Ancestor | Description |
| --- | --- | --- |
| clearContent() | TContainer | Clears its content |
| hide() | TContainer |  |
| loadContent() | TContainer | Loads a content from extrenal URL received in sourceURL parameter |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
| setEnabled() | TContainer | Set component enabled/disabled |
| setVisible() | TContainer |  |
| show() | TContainer |  |

## TColumn

*Inheritance: TComponent / TContainer / TColumn*

**Parent must be: TContainer**

TColumn

### TColumn properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| AJAXQueueID | STRING | Yes | No | Yes | @this.Name | TContainer |  |
| Attributes | TEXT | No | No | No |  | TContainer | HTML Special attributes |
| Class | STRING | No | No | No |  | TContainer | HTML class. Used for injecting extra classes into the control. |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Generate | BOOLEAN | Yes | No | No | true | TContainer |  |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| OffsetExtraSmall | NUMBER | No | No | No |  |  | Bootstrap offsets - col-xs-offset-* List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| OffsetLarge | NUMBER | No | No | No |  |  | Bootstrap offsets - col-lg-offset-* List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| OffsetMedium | NUMBER | No | No | No |  |  | Bootstrap offsets - col-md-offset-* List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| OffsetSmall | NUMBER | No | No | No |  |  | Bootstrap offsets - col-sm-offset-* List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| SizeExtraSmall | NUMBER | No | No | No |  |  | Bootstrap column size - col-xs-* List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| SizeLarge | NUMBER | No | No | No |  |  | Bootstrap column size - col-lg-* List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| SizeMedium | NUMBER | No | No | No |  |  | Bootstrap column size - size-md-* List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| SizeSmall | NUMBER | No | No | No |  |  | Bootstrap column size - col-sm-* List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| SourceURL | STRING | No | No | Yes |  | TContainer | Source URL pointing to the requested resource |
| Style | STRING | No | No | No |  | TContainer | Style information |
| SurroundWithRow | BOOLEAN | Yes | No | No | false |  | Surround column by a bootstrap row |
| Visible | BOOLEAN | Yes | No | Yes | true | TContainer | When true, control is visible, hidden otherwise |

### TColumn events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onClick | GUI | TContainer | onClick event |
| onContentCleared | GUI | TContainer |  |
| onContentLoaded | GUI | TContainer | Fired after loadContent successfully executed |
| onHide | GUI | TContainer | Fired when component transitions from shown to hidden |
| onReady | GUI | TContainer | triggers automatically on page generated |
| onShow | GUI | TContainer | Fired when component transitions from hidden to shown |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TColumn methods

| Name | Ancestor | Description |
| --- | --- | --- |
| clearContent() | TContainer | Clears its content |
| hide() | TContainer |  |
| loadContent() | TContainer | Loads a content from extrenal URL received in sourceURL parameter |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
| setEnabled() | TContainer | Set component enabled/disabled |
| setVisible() | TContainer |  |
| show() | TContainer |  |

## TForm

*Inheritance: TComponent / TContainer / TForm*

**Parent must be: TContainer**

TForm

### TForm properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Action | TAction | No | No | Yes |  |  | Action that this component runs |
| AJAXQueueID | STRING | Yes | No | Yes | @this.Name | TContainer |  |
| Attributes | TEXT | No | No | No |  | TContainer | HTML Special attributes |
| Class | STRING | No | No | No | form-horizontal | TContainer | HTML class. Used for injecting extra classes into the control. |
| CloseModalOnSuccess | BOOLEAN | Yes | No | Yes | true |  | Close parent modal container after successfull submit |
| ConfirmDialog | TConfirmDialog | No | No | Yes |  |  |  |
| ControlSizeExtraSmall | NUMBER | No | No | No |  |  | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ControlSizeLarge | NUMBER | No | No | No |  |  | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ControlSizeMedium | NUMBER | No | No | No | 8 |  | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ControlSizeSmall | NUMBER | No | No | No |  |  | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| EnableAfterSubmit | BOOLEAN | Yes | No | Yes | false |  | Set form enabled after submit |
| EncodingType | STRING | No | No | Yes |  |  | EncodingType attribute specifies how the form-data should be encoded when submitting it to the server. Can only be used when the form method is POST. |
| FormState | STRING | No | Yes | Yes |  |  | List of possible valuesVALIDATION SUBMIT |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Generate | BOOLEAN | Yes | No | Yes | true | TContainer |  |
| LabelSizeExtraSmall | NUMBER | No | No | No |  |  | Visual control label size for extra small screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| LabelSizeLarge | NUMBER | No | No | No |  |  | Visual control label size for large screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| LabelSizeMedium | NUMBER | No | No | No | 4 |  | Visual control label size for medium screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| LabelSizeSmall | NUMBER | No | No | No |  |  | Visual control label size for small screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| Method | STRING | No | No | Yes |  |  | List of possible valuesPOST GET |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | No |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| Style | STRING | No | No | No |  | TContainer | Style information |
| Submitter | TDataProvider | No | No | Yes |  |  | Dataprovider which receives submitted data |
| Target | STRING | No | No | Yes |  |  | HTML Target property |
| URL | STRING | No | No | Yes |  |  | External URL of the resource |
| Validator | TDataProvider | No | No | Yes |  |  |  |
| Visible | BOOLEAN | Yes | No | Yes | true | TContainer | When true, control is visible, hidden otherwise |

### TForm events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onBeforeSubmit | GUI |  |  |
| onCancel | GUI |  |  |
| onClick | GUI | TContainer | onClick event |
| onContentCleared | GUI | TContainer |  |
| onContentLoaded | GUI | TContainer | Fired after loadContent successfully executed |
| onErrorAlert | GUI |  |  |
| onHide | GUI | TContainer | Fired when component transitions from shown to hidden |
| onReady | GUI | TContainer | triggers automatically on page generated |
| onShow | GUI | TContainer | Fired when component transitions from hidden to shown |
| onSubmitError | GUI |  |  |
| onSubmitSuccess | GUI |  |  |
| onSuccessAlert | GUI |  |  |
| onValidateError | GUI |  |  |
| onValidateSuccess | GUI |  |  |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TForm methods

| Name | Ancestor | Description |
| --- | --- | --- |
| cancel() |  |  |
| clearContent() | TContainer | Clears its content |
| hide() | TContainer |  |
| loadContent() | TContainer | Loads a content from extrenal URL received in sourceURL parameter |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
| setEnabled() | TContainer | Set component enabled/disabled |
| setVisible() | TContainer |  |
| show() | TContainer |  |
| submit() |  |  |
| validate() |  |  |

## TFormContainer

*Inheritance: TComponent / TContainer / TFormContainer*

**Parent must be: TComponent**

TFormContainer

### TFormContainer properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| AJAXQueueID | STRING | Yes | No | Yes | @this.Name | TContainer |  |
| Attributes | TEXT | No | No | No |  | TContainer | HTML Special attributes |
| Class | STRING | No | No | No |  | TContainer | HTML class. Used for injecting extra classes into the control. |
| ClearContentOnHide | BOOLEAN | Yes | No | Yes | true |  |  |
| ContainerType | STRING | Yes | No | No | div | TContainer | List of possible valuesdiv li ul ol tr td tbody thead tfoot table span nav section header footer aside |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| DoNotAddCloseTag | BOOLEAN | Yes | No | Yes | false | TContainer | Do not add HTML close tag to container |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Generate | BOOLEAN | Yes | No | No | true | TContainer |  |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| OverrideFormEvents | BOOLEAN | Yes | No | Yes | true |  |  |
| SourceURL | STRING | No | No | Yes |  | TContainer | Source URL pointing to the requested resource |
| Style | STRING | No | No | Yes |  | TContainer | Style information |
| Visible | BOOLEAN | Yes | No | Yes | true | TContainer | When true, control is visible, hidden otherwise |

### TFormContainer events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onClick | GUI | TContainer | onClick event |
| onContentCleared | GUI | TContainer |  |
| onContentLoaded | GUI | TContainer | Fired after loadContent successfully executed |
| onErrorAlert | GUI |  |  |
| onHide | GUI | TContainer | Fired when component transitions from shown to hidden |
| onReady | GUI | TContainer | triggers automatically on page generated |
| onShow | GUI | TContainer | Fired when component transitions from hidden to shown |
| onSubmitSuccess | GUI |  |  |
| onSuccessAlert | GUI |  |  |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TFormContainer methods

| Name | Ancestor | Description |
| --- | --- | --- |
| clearContent() | TContainer | Clears its content |
| hide() | TContainer |  |
| loadContent() | TContainer | Loads a content from extrenal URL received in sourceURL parameter |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
| setEnabled() | TContainer | Set component enabled/disabled |
| setVisible() | TContainer |  |
| show() | TContainer |  |

## TGridRowActions

*Inheritance: TComponent / TContainer / TGridRowActions*

**Parent must be: TComponent**

TGridRowActions

### TGridRowActions properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Align | STRING | Yes | No | No | right |  | Content alignment List of possible valuesleft center right |
| Attributes | TEXT | No | No | No |  | TContainer | HTML Special attributes |
| Class | STRING | No | No | No |  | TContainer | HTML class. Used for injecting extra classes into the control. |
| ColumnOffset | NUMBER | Yes | No | No | 0 |  |  |
| ColumnSpan | NUMBER | No | No | No |  |  | ColumnSpan in table |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Generate | BOOLEAN | Yes | No | No | true | TContainer |  |
| Label | STRING | Yes | No | No | Művelet |  | Label text of the control |
| Name | STRING | No | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | No |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| SourceURL | STRING | No | No | Yes |  | TContainer | Source URL pointing to the requested resource |
| Style | STRING | No | No | No |  | TContainer | Style information |
| Visible | BOOLEAN | Yes | No | Yes | true | TContainer | When true, control is visible, hidden otherwise |

### TGridRowActions events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onClick | GUI | TContainer | onClick event |
| onContentCleared | GUI | TContainer |  |
| onContentLoaded | GUI | TContainer | Fired after loadContent successfully executed |
| onHide | GUI | TContainer | Fired when component transitions from shown to hidden |
| onReady | GUI | TContainer | triggers automatically on page generated |
| onShow | GUI | TContainer | Fired when component transitions from hidden to shown |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TGridRowActions methods

| Name | Ancestor | Description |
| --- | --- | --- |
| clearContent() | TContainer | Clears its content |
| hide() | TContainer |  |
| loadContent() | TContainer | Loads a content from extrenal URL received in sourceURL parameter |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
| setEnabled() | TContainer | Set component enabled/disabled |
| setVisible() | TContainer |  |
| show() | TContainer |  |

## TModal

*Inheritance: TComponent / TContainer / TModal*

**Parent must be: TAction**

TModal - Modal popup

### TModal properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| AJAXQueueID | STRING | Yes | No | Yes | @this.Name | TContainer |  |
| Attributes | TEXT | No | No | No |  | TContainer | HTML Special attributes |
| Class | STRING | No | No | No |  | TContainer | HTML class. Used for injecting extra classes into the control. |
| ClearContentOnHide | BOOLEAN | Yes | No | Yes | true |  |  |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Generate | BOOLEAN | Yes | No | No | true | TContainer |  |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| OverrideFormEvents | BOOLEAN | Yes | No | Yes | true |  |  |
| ShowClose | BOOLEAN | Yes | No | No | trtue |  | Show the "Close" button in the icon toolbar. |
| SourceURL | STRING | No | No | Yes |  | TContainer | Source URL pointing to the requested resource |
| Static | BOOLEAN | Yes | No | Yes | true |  |  |
| Style | STRING | No | No | No |  | TContainer | Style information |
| Title | STRING | No | No | No |  |  |  |

### TModal events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onClick | GUI | TContainer | onClick event |
| onContentCleared | GUI | TContainer |  |
| onContentLoaded | GUI | TContainer | Fired after loadContent successfully executed |
| onErrorAlert | GUI |  |  |
| onHide | GUI | TContainer | Fired when component transitions from shown to hidden |
| onReady | GUI | TContainer | triggers automatically on page generated |
| onShow | GUI | TContainer | Fired when component transitions from hidden to shown |
| onSubmitSuccess | GUI |  |  |
| onSuccessAlert | GUI |  |  |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TModal methods

| Name | Ancestor | Description |
| --- | --- | --- |
| clearContent() | TContainer | Clears its content |
| hide() | TContainer |  |
| loadContent() | TContainer | Loads a content from extrenal URL received in sourceURL parameter |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
| setEnabled() | TContainer | Set component enabled/disabled |
| setVisible() | TContainer |  |
| show() | TContainer |  |

## TPage

*Inheritance: TComponent / TContainer / TPage*

**Parent must be: TApplication**

TPage

### TPage properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Attributes | TEXT | No | No | No |  | TContainer | HTML Special attributes |
| Class | STRING | No | No | No |  | TContainer | HTML class. Used for injecting extra classes into the control. |
| CSS | TEXT | No | No | No |  |  | CSS |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | No |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| SourceURL | STRING | No | No | Yes |  | TContainer | Source URL pointing to the requested resource |
| Style | STRING | No | No | No |  | TContainer | Style information |
| Template | TEMPLATE | Yes | No | No |  |  | Template for freestyle formatting :) |

### TPage events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onClick | GUI | TContainer | onClick event |
| onContentCleared | GUI | TContainer |  |
| onContentLoaded | GUI | TContainer | Fired after loadContent successfully executed |
| onHide | GUI | TContainer | Fired when component transitions from shown to hidden |
| onReady | GUI | TContainer | triggers automatically on page generated |
| onShow | GUI | TContainer | Fired when component transitions from hidden to shown |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TPage methods

| Name | Ancestor | Description |
| --- | --- | --- |
| clearContent() | TContainer | Clears its content |
| hide() | TContainer |  |
| loadContent() | TContainer | Loads a content from extrenal URL received in sourceURL parameter |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
| setEnabled() | TContainer | Set component enabled/disabled |
| setVisible() | TContainer |  |
| show() | TContainer |  |

## TPDFPage

*Inheritance: TComponent / TContainer / TPage / TPDFPage*

**Parent must be: TApplication**

TPDFPage

### TPDFPage properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Attributes | TEXT | No | No | No |  | TContainer | HTML Special attributes |
| BodyContainerName | STRING | Yes | No | Yes |  |  | Page body container |
| Class | STRING | No | No | No |  | TContainer | HTML class. Used for injecting extra classes into the control. |
| CSS | TEXT | No | No | Yes |  | TPage | CSS |
| CSSFile | STRING | No | No | Yes |  |  |  |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| Download | BOOLEAN | Yes | No | No | false |  | Download PDF |
| FileName | STRING | No | No | No |  |  | Filename of generated PDF |
| FooterContainerName | STRING | No | No | Yes |  |  | Footer container component |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| HeaderContainerName | STRING | No | No | No |  |  | Header container component |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | No |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| PAdES | TPAdES | No | No | Yes |  |  |  |
| PDFConfig | JSON | Yes | No | No | {"mode":"A4"} |  | mPDF configuration |
| SourceURL | STRING | No | No | No |  | TContainer | Source URL pointing to the requested resource |
| Style | STRING | No | No | No |  | TContainer | Style information |
| Template | TEMPLATE | Yes | No | No |  | TPage | Template for freestyle formatting :) |
| Watermark | STRING | No | No | Yes |  |  | Watermark |

### TPDFPage events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onClick | GUI | TContainer | onClick event |
| onContentCleared | GUI | TContainer |  |
| onContentLoaded | GUI | TContainer | Fired after loadContent successfully executed |
| onHide | GUI | TContainer | Fired when component transitions from shown to hidden |
| onReady | GUI | TContainer | triggers automatically on page generated |
| onShow | GUI | TContainer | Fired when component transitions from hidden to shown |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TPDFPage methods

| Name | Ancestor | Description |
| --- | --- | --- |
| clearContent() | TContainer | Clears its content |
| hide() | TContainer |  |
| loadContent() | TContainer | Loads a content from extrenal URL received in sourceURL parameter |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
| setEnabled() | TContainer | Set component enabled/disabled |
| setVisible() | TContainer |  |
| show() | TContainer |  |

## TTabPane

*Inheritance: TComponent / TContainer / TTabPane*

**Parent must be: TTabs**

TTabPane

### TTabPane properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| AJAXQueueID | STRING | Yes | No | Yes | @this.Name | TContainer |  |
| Attributes | TEXT | No | No | No |  | TContainer | HTML Special attributes |
| Class | STRING | No | No | No |  | TContainer | HTML class. Used for injecting extra classes into the control. |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FirstLoad | BOOLEAN | No | Yes | Yes | true |  | Runtime property indicating first appearance of the component |
| FunctionCode | STRING | Yes | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Generate | BOOLEAN | Yes | No | No | true | TContainer |  |
| Icon | STRING | No | No | No |  |  | Icon specified in the icon's library format, eg. "fa fa-share" or "glyphicon glyphicon-share" |
| IconAlign | STRING | No | No | No |  |  | Alignment of the icon. Left means before the component's label or content, right means after it. Defaults to left. List of possible valuesleft right |
| Label | STRING | No | No | Yes |  |  | Label text of the control |
| LabelClass | STRING | No | No | No |  |  |  |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | No |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| SourceURL | STRING | No | No | Yes |  | TContainer | Source URL pointing to the requested resource |
| Style | STRING | No | No | No |  | TContainer | Style information |
| Visible | BOOLEAN | Yes | No | Yes | true | TContainer | When true, control is visible, hidden otherwise |

### TTabPane events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onActivate | GUI |  | Fired when component has been activated |
| onClick | GUI | TContainer | onClick event |
| onContentCleared | GUI | TContainer |  |
| onContentLoaded | GUI | TContainer | Fired after loadContent successfully executed |
| onDeactivate | GUI |  | Fired when component is deactivated |
| onHide | GUI | TContainer | Fired when component transitions from shown to hidden |
| onReady | GUI | TContainer | triggers automatically on page generated |
| onShow | GUI | TContainer | Fired when component transitions from hidden to shown |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TTabPane methods

| Name | Ancestor | Description |
| --- | --- | --- |
| activate() |  |  |
| clearContent() | TContainer | Clears its content |
| hide() | TContainer |  |
| loadContent() | TContainer | Loads a content from extrenal URL received in sourceURL parameter |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
| setEnabled() | TContainer | Set component enabled/disabled |
| setLabel() |  | Sets the label text of a component |
| setVisible() | TContainer |  |
| show() | TContainer |  |

## TTabs

*Inheritance: TComponent / TContainer / TTabs*

**Parent must be: TContainer**

TTabs

### TTabs properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| AJAXQueueID | STRING | Yes | No | Yes | @this.Name | TContainer |  |
| Attributes | TEXT | No | No | No |  | TContainer | HTML Special attributes |
| ContainerClass | STRING | No | No | No |  |  |  |
| DefaultTabPane | TTabPane | No | No | Yes |  |  | Default tab pane to show |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Generate | BOOLEAN | Yes | No | No | true | TContainer |  |
| HeaderClass | STRING | No | No | No |  |  | Class of the component's header |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| RememberTabPane | BOOLEAN | Yes | No | No | true |  |  |
| SourceURL | STRING | No | No | Yes |  | TContainer | Source URL pointing to the requested resource |
| Visible | BOOLEAN | Yes | No | Yes | true | TContainer | When true, control is visible, hidden otherwise |

### TTabs events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterTabShow | GUI |  |  |
| onBeforeTabShow | GUI |  |  |
| onClick | GUI | TContainer | onClick event |
| onContentCleared | GUI | TContainer |  |
| onContentLoaded | GUI | TContainer | Fired after loadContent successfully executed |
| onHide | GUI | TContainer | Fired when component transitions from shown to hidden |
| onReady | GUI | TContainer | triggers automatically on page generated |
| onShow | GUI | TContainer | Fired when component transitions from hidden to shown |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TTabs methods

| Name | Ancestor | Description |
| --- | --- | --- |
| clearContent() | TContainer | Clears its content |
| hide() | TContainer |  |
| loadContent() | TContainer | Loads a content from extrenal URL received in sourceURL parameter |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
| setEnabled() | TContainer | Set component enabled/disabled |
| setVisible() | TContainer |  |
| show() | TContainer |  |

## TWidget

*Inheritance: TComponent / TContainer / TWidget*

**Parent must be: TContainer**

TWidget

### TWidget properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| AJAXQueueID | STRING | Yes | No | Yes | @this.Name | TContainer |  |
| Attributes | TEXT | No | No | No |  | TContainer | HTML Special attributes |
| Class | STRING | No | No | No |  | TContainer | HTML class. Used for injecting extra classes into the control. |
| Collapsible | STRING | Yes | No | No | false |  | Defines whether the component can be collapsed and if so, what its initial state List of possible valuesfalse InitialClosed InitialOpen |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| Disposable | BOOLEAN | Yes | No | No | false |  | Defines whether the component can be disposed |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Generate | BOOLEAN | Yes | No | No | true | TContainer |  |
| HeaderClass | STRING | No | No | No |  |  | Class of the component's header |
| HeaderControlsParameter | STRING | No | No | No |  |  |  |
| Icon | STRING | No | No | No |  |  | Icon specified in the icon's library format, eg. "fa fa-share" or "glyphicon glyphicon-share" |
| IconAlign | STRING | No | No | No |  |  | Alignment of the icon. Left means before the component's label or content, right means after it. Defaults to left. List of possible valuesleft right |
| Label | STRING | No | No | Yes |  |  | Label text of the control |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| SourceURL | STRING | No | No | Yes |  | TContainer | Source URL pointing to the requested resource |
| Style | STRING | No | No | No |  | TContainer | Style information |
| Visible | BOOLEAN | Yes | No | Yes | true | TContainer | When true, control is visible, hidden otherwise |

### TWidget events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onClick | GUI | TContainer | onClick event |
| onCollapse | GUI |  | Component is being collapsed |
| onContentCleared | GUI | TContainer |  |
| onContentLoaded | GUI | TContainer | Fired after loadContent successfully executed |
| onExpand | GUI |  | Component is being expanded |
| onHide | GUI | TContainer | Fired when component transitions from shown to hidden |
| onReady | GUI | TContainer | triggers automatically on page generated |
| onShow | GUI | TContainer | Fired when component transitions from hidden to shown |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TWidget methods

| Name | Ancestor | Description |
| --- | --- | --- |
| clearContent() | TContainer | Clears its content |
| hide() | TContainer |  |
| loadContent() | TContainer | Loads a content from extrenal URL received in sourceURL parameter |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
| setEnabled() | TContainer | Set component enabled/disabled |
| setVisible() | TContainer |  |
| show() | TContainer |  |

## TWizard

*Inheritance: TComponent / TContainer / TWizard*

**Parent must be: TContainer**

TWizard

### TWizard properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| AJAXQueueID | STRING | Yes | No | Yes | @this.Name | TContainer |  |
| Attributes | TEXT | No | No | No |  | TContainer | HTML Special attributes |
| BtnFinishLabel | STRING | Yes | No | No | [:WIZARD.BUTTON.FINISH,Véglegesítés:] |  |  |
| BtnNextClass | STRING | Yes | No | No | btn-default btn-sm |  |  |
| BtnNextIcon | STRING | Yes | No | No | fa fa-angle-right |  |  |
| BtnNextLabel | STRING | Yes | No | No | [:WIZARD.BUTTON.NEXT,Következő:] |  |  |
| BtnPrevClass | STRING | Yes | No | No | btn-default btn-sm |  |  |
| BtnPrevIcon | STRING | Yes | No | No | fa fa-angle-left |  |  |
| BtnPrevLabel | STRING | Yes | No | No | [:WIZARD.BUTTON.PREV,Előző:] |  |  |
| Class | STRING | No | No | No |  | TContainer | HTML class. Used for injecting extra classes into the control. |
| DefaultWizardStep | TWizardStep | No | No | Yes |  |  |  |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| DisableBackButton | BOOLEAN | Yes | No | Yes | false |  |  |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Generate | BOOLEAN | Yes | No | Yes | true | TContainer |  |
| Name | STRING | No | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| RememberWizardStep | BOOLEAN | Yes | No | Yes | true |  |  |
| SourceURL | STRING | No | No | Yes |  | TContainer | Source URL pointing to the requested resource |
| Style | STRING | No | No | No |  | TContainer | Style information |
| Visible | BOOLEAN | Yes | No | Yes | true | TContainer | When true, control is visible, hidden otherwise |

### TWizard events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterStepShow | GUI |  |  |
| onBeforeStepShow | GUI |  |  |
| onClick | GUI | TContainer | onClick event |
| onContentCleared | GUI | TContainer |  |
| onContentLoaded | GUI | TContainer | Fired after loadContent successfully executed |
| onHide | GUI | TContainer | Fired when component transitions from shown to hidden |
| onReady | GUI | TContainer | triggers automatically on page generated |
| onShow | GUI | TContainer | Fired when component transitions from hidden to shown |
| onWizardComplete | GUI |  |  |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TWizard methods

| Name | Ancestor | Description |
| --- | --- | --- |
| clearContent() | TContainer | Clears its content |
| first() |  |  |
| hide() | TContainer |  |
| loadContent() | TContainer | Loads a content from extrenal URL received in sourceURL parameter |
| moveTo() |  |  |
| next() |  |  |
| previous() |  |  |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
| setEnabled() | TContainer | Set component enabled/disabled |
| setVisible() | TContainer |  |
| show() | TContainer |  |

## TWizardStep

*Inheritance: TComponent / TContainer / TWizardStep*

**Parent must be: TWizard**

TWizardStep

### TWizardStep properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| AJAXQueueID | STRING | Yes | No | Yes | @this.Name | TContainer |  |
| Attributes | TEXT | No | No | No |  | TContainer | HTML Special attributes |
| Class | STRING | No | No | No |  | TContainer | HTML class. Used for injecting extra classes into the control. |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Generate | BOOLEAN | Yes | No | No | true | TContainer |  |
| Label | STRING | Yes | No | Yes |  |  | Label text of the control |
| Name | STRING | No | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| Sequence | NUMBER | Yes | No | Yes |  |  |  |
| SourceURL | STRING | No | No | Yes |  | TContainer | Source URL pointing to the requested resource |
| Style | STRING | No | No | No |  | TContainer | Style information |
| Visible | BOOLEAN | Yes | No | Yes | true | TContainer | When true, control is visible, hidden otherwise |

### TWizardStep events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onClick | GUI | TContainer | onClick event |
| onContentCleared | GUI | TContainer |  |
| onContentLoaded | GUI | TContainer | Fired after loadContent successfully executed |
| onHide | GUI | TContainer | Fired when component transitions from shown to hidden |
| onReady | GUI | TContainer | triggers automatically on page generated |
| onShow | GUI | TContainer | Fired when component transitions from hidden to shown |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TWizardStep methods

| Name | Ancestor | Description |
| --- | --- | --- |
| clearContent() | TContainer | Clears its content |
| hide() | TContainer |  |
| loadContent() | TContainer | Loads a content from extrenal URL received in sourceURL parameter |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
| setEnabled() | TContainer | Set component enabled/disabled |
| setVisible() | TContainer |  |
| show() | TContainer |  |

## TControl

*Inheritance: TComponent / TControl*

**Parent must be: only inherited types can be placed**

TControl

### TControl properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Attributes | TEXT | No | No | Yes |  |  | HTML Special attributes |
| Class | STRING | No | No | Yes |  |  | HTML class. Used for injecting extra classes into the control. |
| DBField | TDBField | No | No | Yes |  |  | Database field attached to the control. When specified, the control is database-aware. |
| DBParameterName | STRING | No | No | Yes |  |  |  |
| DBValue | STRING | No | Yes | Yes |  |  |  |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| Enabled | BOOLEAN | Yes | No | Yes |  |  | When true (default), control is enabled. Otherwise control is disabled. |
| FunctionCode | STRING | No | No | Yes |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| HelpText | TEXT | No | No | Yes |  |  | Help text to be displayed for the control |
| Label | STRING | No | No | Yes |  |  | Label text of the control |
| LabelClass | STRING | No | No | Yes |  |  |  |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| ReadOnly | BOOLEAN | No | No | Yes | false |  | List of possible valuesfalse true |
| Style | STRING | No | No | Yes |  |  | Style information |
| Value | STRING | No | No | Yes |  |  | Text value of the control |
| Visible | BOOLEAN | Yes | No | Yes |  |  | When true, control is visible, hidden otherwise |

### TControl events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterCreate | GUI |  |  |
| onBeforeCreate | GUI |  |  |
| onBlur | GUI |  | Fired when a user leaves an input field |
| onChange | GUI |  | Value change event |
| onClick | GUI |  | onClick event |
| onConfirmFalse | GUI |  |  |
| onConfirmTrue | GUI |  |  |
| onHide | GUI |  | Fired when component transitions from shown to hidden |
| onMouseDown | GUI |  |  |
| onMouseOut | GUI |  |  |
| onMouseOver | GUI |  |  |
| onMouseUp | GUI |  |  |
| onShow | GUI |  | Fired when component transitions from hidden to shown |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TControl methods

| Name | Ancestor | Description |
| --- | --- | --- |
| parseControlParameters() |  | Parses and executes a number of control parameters |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
| setEnabled() |  | Set component enabled/disabled |
| setLabel() |  | Sets the label text of a component |
| setReadOnly() |  |  |
| setRequired() |  |  |
| setValue() |  |  |
| setVisible() |  |  |

## TButton

*Inheritance: TComponent / TControl / TButton*

**Parent must be: TContainer**

TButton

### TButton properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Attributes | TEXT | No | No | Yes |  | TControl | HTML Special attributes |
| BootstrapSize | STRING | No | No | No |  |  | Twitter Bootstrap compliant standard control size as follows: xs (extra small), sm (small) and lg (large). When not specified control has normal size. List of possible valuesxs sm lg |
| Class | STRING | No | No | No |  | TControl | HTML class. Used for injecting extra classes into the control. |
| ConfirmDialog | TConfirmDialog | No | No | Yes |  |  |  |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| Enabled | BOOLEAN | Yes | No | Yes | true | TControl | When true (default), control is enabled. Otherwise control is disabled. |
| FunctionCode | STRING | Yes | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| HelpText | TEXT | No | No | No |  | TControl | Help text to be displayed for the control |
| Icon | STRING | No | No | No |  |  | Icon specified in the icon's library format, eg. "fa fa-share" or "glyphicon glyphicon-share" |
| IconAlign | STRING | No | No | No |  |  | Alignment of the icon. Left means before the component's label or content, right means after it. Defaults to left. List of possible valuesleft right |
| Label | STRING | No | No | No |  | TControl | Label text of the control |
| LabelClass | STRING | No | No | No |  | TControl |  |
| MiddleClick | BOOLEAN | Yes | No | No | false |  | Enable middle click support on component's click event. |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| Style | STRING | No | No | No |  | TControl | Style information |
| Visible | BOOLEAN | Yes | No | Yes | true | TControl | When true, control is visible, hidden otherwise |

### TButton events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterCreate | GUI | TControl |  |
| onBeforeCreate | GUI | TControl |  |
| onBlur | GUI | TControl | Fired when a user leaves an input field |
| onChange | GUI | TControl | Value change event |
| onClick | GUI | TControl | onClick event |
| onConfirmFalse | GUI | TControl |  |
| onConfirmTrue | GUI | TControl |  |
| onHide | GUI | TControl | Fired when component transitions from shown to hidden |
| onMouseDown | GUI | TControl |  |
| onMouseOut | GUI | TControl |  |
| onMouseOver | GUI | TControl |  |
| onMouseUp | GUI | TControl |  |
| onShow | GUI | TControl | Fired when component transitions from hidden to shown |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TButton methods

| Name | Ancestor | Description |
| --- | --- | --- |
| click() |  | Click method |
| parseControlParameters() | TControl | Parses and executes a number of control parameters |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
| setEnabled() | TControl | Set component enabled/disabled |
| setLabel() | TControl | Sets the label text of a component |
| setReadOnly() | TControl |  |
| setRequired() | TControl |  |
| setValue() | TControl |  |
| setVisible() | TControl |  |

## TButtonDropdown

*Inheritance: TComponent / TControl / TButton / TButtonDropdown*

**Parent must be: TContainer**

TButtonDropdown

### TButtonDropdown properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Attributes | TEXT | No | No | Yes |  | TControl | HTML Special attributes |
| BootstrapSize | STRING | No | No | No |  | TButton | Twitter Bootstrap compliant standard control size as follows: xs (extra small), sm (small) and lg (large). When not specified control has normal size. List of possible valuesxs sm lg |
| Class | STRING | No | No | No |  | TControl | HTML class. Used for injecting extra classes into the control. |
| ConfirmDialog | TConfirmDialog | No | No | Yes |  | TButton |  |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| DropDirection | STRING | No | No | No |  |  | List of possible valuesdropdown dropup |
| DropdownClass | STRING | No | No | No |  |  |  |
| Enabled | BOOLEAN | Yes | No | Yes | true | TControl | When true (default), control is enabled. Otherwise control is disabled. |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| HelpText | TEXT | No | No | No |  | TControl | Help text to be displayed for the control |
| Icon | STRING | No | No | No |  | TButton | Icon specified in the icon's library format, eg. "fa fa-share" or "glyphicon glyphicon-share" |
| IconAlign | STRING | No | No | No | left | TButton | Alignment of the icon. Left means before the component's label or content, right means after it. Defaults to left. List of possible valuesleft right |
| Label | STRING | No | No | No |  | TControl | Label text of the control |
| LabelClass | STRING | No | No | No |  | TControl |  |
| MiddleClick | BOOLEAN | No | Yes | No | false | TButton | Enable middle click support on component's click event. |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| ReadOnly | BOOLEAN | Yes | No | Yes | false | TControl | List of possible valuesfalse true |
| Style | STRING | No | No | No |  | TControl | Style information |
| Visible | BOOLEAN | Yes | No | Yes | true | TControl | When true, control is visible, hidden otherwise |

### TButtonDropdown events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterCreate | GUI | TControl |  |
| onBeforeCreate | GUI | TControl |  |
| onBlur | GUI | TControl | Fired when a user leaves an input field |
| onChange | GUI | TControl | Value change event |
| onClick | GUI | TControl | onClick event |
| onConfirmFalse | GUI | TControl |  |
| onConfirmTrue | GUI | TControl |  |
| onHide | GUI | TControl | Fired when component transitions from shown to hidden |
| onMouseDown | GUI | TControl |  |
| onMouseOut | GUI | TControl |  |
| onMouseOver | GUI | TControl |  |
| onMouseUp | GUI | TControl |  |
| onShow | GUI | TControl | Fired when component transitions from hidden to shown |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TButtonDropdown methods

| Name | Ancestor | Description |
| --- | --- | --- |
| click() | TButton | Click method |
| parseControlParameters() | TControl | Parses and executes a number of control parameters |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
| setEnabled() | TControl | Set component enabled/disabled |
| setLabel() | TControl | Sets the label text of a component |
| setReadOnly() | TControl |  |
| setRequired() | TControl |  |
| setValue() | TControl |  |
| setVisible() | TControl |  |

## TFormControl

*Inheritance: TComponent / TControl / TFormControl*

**Parent must be: must be a root element of application structure**

TFormControl

### TFormControl properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Attributes | TEXT | No | No | Yes |  | TControl | HTML Special attributes |
| Class | STRING | No | No | Yes |  | TControl | HTML class. Used for injecting extra classes into the control. |
| ControlSizeExtraSmall | NUMBER | No | No | Yes |  |  | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ControlSizeLarge | NUMBER | No | No | Yes |  |  | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ControlSizeMedium | NUMBER | No | No | Yes | 8 |  | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ControlSizeSmall | NUMBER | No | No | Yes |  |  | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| DBField | TDBField | No | No | Yes |  | TControl | Database field attached to the control. When specified, the control is database-aware. |
| DBParameterName | STRING | No | No | Yes |  | TControl |  |
| DBValue | STRING | No | Yes | Yes |  | TControl |  |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| Enabled | BOOLEAN | No | No | Yes |  | TControl | When true (default), control is enabled. Otherwise control is disabled. |
| FunctionCode | STRING | No | No | Yes |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| HelpText | TEXT | No | No | Yes |  | TControl | Help text to be displayed for the control |
| Label | STRING | No | No | Yes |  | TControl | Label text of the control |
| LabelClass | STRING | No | No | Yes |  | TControl |  |
| LabelSizeExtraSmall | NUMBER | No | No | Yes |  |  | Visual control label size for extra small screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| LabelSizeLarge | NUMBER | No | No | Yes |  |  | Visual control label size for large screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| LabelSizeMedium | NUMBER | No | No | Yes | 4 |  | Visual control label size for medium screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| LabelSizeSmall | NUMBER | No | No | Yes |  |  | Visual control label size for small screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| PlaceHolder | STRING | No | No | Yes |  |  | When control value is not specified, placeholder text will be displayed. Not supported by all controls. |
| ReadOnly | BOOLEAN | No | No | Yes |  | TControl | List of possible valuesfalse true |
| Required | BOOLEAN | No | No | Yes |  |  | Control is a required input |
| RowClass | STRING | No | No | Yes |  |  |  |
| RowStyle | STRING | No | No | Yes |  |  |  |
| Style | STRING | No | No | Yes |  | TControl | Style information |
| Value | STRING | No | No | Yes |  | TControl | Text value of the control |
| Visible | BOOLEAN | No | No | Yes |  | TControl | When true, control is visible, hidden otherwise |

### TFormControl events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterCreate | GUI | TControl |  |
| onBeforeCreate | GUI | TControl |  |
| onBlur | GUI | TControl | Fired when a user leaves an input field |
| onChange | GUI | TControl | Value change event |
| onClick | GUI | TControl | onClick event |
| onConfirmFalse | GUI | TControl |  |
| onConfirmTrue | GUI | TControl |  |
| onHide | GUI | TControl | Fired when component transitions from shown to hidden |
| onMouseDown | GUI | TControl |  |
| onMouseOut | GUI | TControl |  |
| onMouseOver | GUI | TControl |  |
| onMouseUp | GUI | TControl |  |
| onShow | GUI | TControl | Fired when component transitions from hidden to shown |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TFormControl methods

| Name | Ancestor | Description |
| --- | --- | --- |
| parseControlParameters() | TControl | Parses and executes a number of control parameters |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
| setEnabled() | TControl | Set component enabled/disabled |
| setLabel() | TControl | Sets the label text of a component |
| setReadOnly() | TControl |  |
| setRequired() | TControl |  |
| setValue() | TControl |  |
| setVisible() | TControl |  |

## TCheckbox

*Inheritance: TComponent / TControl / TFormControl / TCheckbox*

**Parent must be: TContainer**

TCheckbox

### TCheckbox properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Attributes | TEXT | No | No | Yes |  | TControl | HTML Special attributes |
| Class | STRING | No | No | No |  | TControl | HTML class. Used for injecting extra classes into the control. |
| ControlSizeExtraSmall | NUMBER | No | No | No | @(TForm).ControlSizeExtraSmall | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ControlSizeLarge | NUMBER | No | No | No | @(TForm).ControlSizeLarge | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ControlSizeMedium | NUMBER | No | No | No | @(TForm).ControlSizeMedium | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ControlSizeSmall | NUMBER | No | No | No | @(TForm).ControlSizeSmall | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| DBField | TDBField | No | No | Yes |  | TControl | Database field attached to the control. When specified, the control is database-aware. |
| DBParameterName | STRING | No | No | Yes |  | TControl |  |
| DBValue | STRING | No | Yes | Yes |  | TControl |  |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| Enabled | BOOLEAN | Yes | No | Yes | true | TControl | When true (default), control is enabled. Otherwise control is disabled. |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| HelpText | TEXT | No | No | No |  | TControl | Help text to be displayed for the control |
| Label | STRING | No | No | No | @this.DBField.Label | TControl | Label text of the control |
| LabelClass | STRING | No | No | No |  | TControl |  |
| LabelSizeExtraSmall | NUMBER | No | No | No | @(TForm).LabelSizeExtraSmall | TFormControl | Visual control label size for extra small screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| LabelSizeLarge | NUMBER | No | No | No | @(TForm).LabelSizeLarge | TFormControl | Visual control label size for large screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| LabelSizeMedium | NUMBER | No | No | No | @(TForm).LabelSizeMedium | TFormControl | Visual control label size for medium screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| LabelSizeSmall | NUMBER | No | No | No | @(TForm).LabelSizeSmall | TFormControl | Visual control label size for small screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| PlaceHolder | STRING | No | No | No |  | TFormControl | When control value is not specified, placeholder text will be displayed. Not supported by all controls. |
| ReadOnly | BOOLEAN | Yes | No | Yes | false | TControl | List of possible valuesfalse true |
| Required | BOOLEAN | No | Yes | Yes | false | TFormControl | Control is a required input |
| RowClass | STRING | No | No | Yes |  | TFormControl |  |
| RowStyle | STRING | No | No | Yes |  | TFormControl |  |
| Style | STRING | No | No | No |  | TControl | Style information |
| Value | STRING | No | No | Yes |  | TControl | Text value of the control |
| ValueChecked | STRING | Yes | No | Yes | Y |  | String representation of the value of checkbox checked, eg. Y |
| ValueUnchecked | STRING | Yes | No | Yes | N |  | String representation of the value of checkbox unchecked, eg. N |
| Visible | BOOLEAN | Yes | No | Yes | true | TControl | When true, control is visible, hidden otherwise |

### TCheckbox events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterCreate | GUI | TControl |  |
| onBeforeCreate | GUI | TControl |  |
| onBlur | GUI | TControl | Fired when a user leaves an input field |
| onChange | GUI | TControl | Value change event |
| onChecked | GUI |  |  |
| onClick | GUI | TControl | onClick event |
| onConfirmFalse | GUI | TControl |  |
| onConfirmTrue | GUI | TControl |  |
| onHide | GUI | TControl | Fired when component transitions from shown to hidden |
| onMouseDown | GUI | TControl |  |
| onMouseOut | GUI | TControl |  |
| onMouseOver | GUI | TControl |  |
| onMouseUp | GUI | TControl |  |
| onShow | GUI | TControl | Fired when component transitions from hidden to shown |
| onUnchecked | GUI |  |  |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TCheckbox methods

| Name | Ancestor | Description |
| --- | --- | --- |
| parseControlParameters() | TControl | Parses and executes a number of control parameters |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
| setEnabled() | TControl | Set component enabled/disabled |
| setLabel() | TControl | Sets the label text of a component |
| setReadOnly() | TControl |  |
| setRequired() | TControl |  |
| setValue() | TControl |  |
| setVisible() | TControl |  |

## TDateTimePicker

*Inheritance: TComponent / TControl / TFormControl / TDateTimePicker*

**Parent must be: TContainer**

TDateTimePicker

### TDateTimePicker properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Attributes | TEXT | No | No | Yes |  | TControl | HTML Special attributes |
| CalendarIcon | STRING | No | No | No |  |  | Icon displayed next to the input field of DateTimePicker |
| Class | STRING | No | No | No |  | TControl | HTML class. Used for injecting extra classes into the control. |
| ControlSizeExtraSmall | NUMBER | No | No | No | @(TForm).ControlSizeExtraSmall | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ControlSizeLarge | NUMBER | No | No | No | @(TForm).ControlSizeLarge | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ControlSizeMedium | NUMBER | No | No | No | @(TForm).ControlSizeMedium | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ControlSizeSmall | NUMBER | No | No | No | @(TForm).ControlSizeSmall | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| DBField | TDBField | No | No | Yes |  | TControl | Database field attached to the control. When specified, the control is database-aware. |
| DBParameterName | STRING | No | No | Yes |  | TControl |  |
| DBValue | STRING | No | Yes | Yes |  | TControl |  |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| DisabledDaysOfWeek | STRING | No | No | No |  |  | Disables the section of days of the week, e.g. weekends. Default is [] and accepts an array of numbers from 0-6. |
| DisabledHours | STRING | No | No | No |  |  | ill allow or disallow hour selections but will affect all days |
| Enabled | BOOLEAN | Yes | No | Yes | true | TControl | When true (default), control is enabled. Otherwise control is disabled. |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| HelpText | TEXT | No | No | No |  | TControl | Help text to be displayed for the control |
| JSDateTimeFormat | STRING | Yes | No | Yes | @(parameter).JSDateTimeHMFormat |  |  |
| Label | STRING | No | No | No | @this.DBField.Label | TControl | Label text of the control |
| LabelClass | STRING | No | No | No |  | TControl |  |
| LabelSizeExtraSmall | NUMBER | No | No | No | @(TForm).LabelSizeExtraSmall | TFormControl | Visual control label size for extra small screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| LabelSizeLarge | NUMBER | No | No | No | @(TForm).LabelSizeLarge | TFormControl | Visual control label size for large screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| LabelSizeMedium | NUMBER | No | No | No | @(TForm).LabelSizeMedium | TFormControl | Visual control label size for medium screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| LabelSizeSmall | NUMBER | No | No | No | @(TForm).LabelSizeSmall | TFormControl | Visual control label size for small screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| PlaceHolder | STRING | No | No | No |  | TFormControl | When control value is not specified, placeholder text will be displayed. Not supported by all controls. |
| PosHorizontal | STRING | No | No | No |  |  | List of possible valuesauto left right |
| PosVertical | STRING | No | No | No |  |  | List of possible valuesauto top bottom |
| ReadOnly | BOOLEAN | Yes | No | Yes | false | TControl | List of possible valuesfalse true |
| Required | BOOLEAN | Yes | No | Yes | false | TFormControl | Control is a required input |
| RowClass | STRING | No | No | Yes |  | TFormControl |  |
| RowStyle | STRING | No | No | Yes |  | TFormControl |  |
| ShowCalendarWeeks | BOOLEAN | Yes | No | No | true |  | Show week number at the beginning of each line |
| ShowClear | BOOLEAN | Yes | No | No | true |  | Show the "Clear" button in the icon toolbar. Clicking the "Clear" button will set the calendar to null. |
| ShowClose | BOOLEAN | Yes | No | No | true |  | Show the "Close" button in the icon toolbar. |
| ShowOnFocus | BOOLEAN | Yes | No | No | true |  | Show DateTimePicker component when component receives focus. |
| ShowToday | BOOLEAN | Yes | No | No | true |  | Show the "Today" button in the icon toolbar. Clicking the "Today" button will set the calendar view and set the date to now. |
| SideBySide | BOOLEAN | Yes | No | No | true |  | When true date and time pickers are displayed on a single page, side-by-side. When false, one has to switch between date and time picker forms. |
| Stepping | NUMBER | No | No | No |  |  | Minute stepping in time picker, defaults to 1 |
| Style | STRING | No | No | No |  | TControl | Style information |
| Value | STRING | No | No | Yes |  | TControl | Text value of the control |
| Visible | BOOLEAN | Yes | No | Yes | true | TControl | When true, control is visible, hidden otherwise |

### TDateTimePicker events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterCreate | GUI | TControl |  |
| onBeforeCreate | GUI | TControl |  |
| onBlur | GUI | TControl | Fired when a user leaves an input field |
| onChange | GUI | TControl | Value change event |
| onClick | GUI | TControl | onClick event |
| onConfirmFalse | GUI | TControl |  |
| onConfirmTrue | GUI | TControl |  |
| onHide | GUI | TControl | Fired when component transitions from shown to hidden |
| onMouseDown | GUI | TControl |  |
| onMouseOut | GUI | TControl |  |
| onMouseOver | GUI | TControl |  |
| onMouseUp | GUI | TControl |  |
| onShow | GUI | TControl | Fired when component transitions from hidden to shown |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TDateTimePicker methods

| Name | Ancestor | Description |
| --- | --- | --- |
| parseControlParameters() | TControl | Parses and executes a number of control parameters |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
| setEnabled() | TControl | Set component enabled/disabled |
| setLabel() | TControl | Sets the label text of a component |
| setReadOnly() | TControl |  |
| setRequired() | TControl |  |
| setValue() | TControl |  |
| setVisible() | TControl |  |

## TEdit

*Inheritance: TComponent / TControl / TFormControl / TEdit*

**Parent must be: TContainer**

TEdit

### TEdit properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Align | STRING | Yes | No | No | left |  | Content alignment List of possible valuesleft center right |
| Attributes | TEXT | No | No | Yes |  | TControl | HTML Special attributes |
| AutoComplete | BOOLEAN | Yes | No | No | true |  | HTML AutoComplete on or off. Defaults to on. |
| Class | STRING | No | No | No |  | TControl | HTML class. Used for injecting extra classes into the control. |
| ControlSizeExtraSmall | NUMBER | No | No | No | @(TForm).ControlSizeExtraSmall | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ControlSizeLarge | NUMBER | No | No | No | @(TForm).ControlSizeLarge | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ControlSizeMedium | NUMBER | No | No | No | @(TForm).ControlSizeMedium | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ControlSizeSmall | NUMBER | No | No | No | @(TForm).ControlSizeSmall | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| DBField | TDBField | No | No | Yes |  | TControl | Database field attached to the control. When specified, the control is database-aware. |
| DBParameterName | STRING | No | No | Yes |  | TControl |  |
| DBValue | STRING | No | Yes | Yes |  | TControl |  |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| Enabled | BOOLEAN | Yes | No | Yes | true | TControl | When true (default), control is enabled. Otherwise control is disabled. |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| HelpText | TEXT | No | No | No |  | TControl | Help text to be displayed for the control |
| HTMLInputType | STRING | No | No | No |  |  | Standard HTML input type, defaults to text List of possible valuestext password email hidden number button checkbox color date datetime datetime-local file image month radio range reset search submit tel time url week |
| Icon | STRING | No | No | No |  |  | Icon specified in the icon's library format, eg. "fa fa-share" or "glyphicon glyphicon-share" |
| IconAlign | STRING | No | No | No |  |  | Alignment of the icon. Left means before the component's label or content, right means after it. Defaults to left. List of possible valuesleft right |
| Label | STRING | No | No | No | @this.DBField.Label | TControl | Label text of the control |
| LabelClass | STRING | No | No | No |  | TControl |  |
| LabelSizeExtraSmall | NUMBER | No | No | No | @(TForm).LabelSizeExtraSmall | TFormControl | Visual control label size for extra small screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| LabelSizeLarge | NUMBER | No | No | No | @(TForm).LabelSizeLarge | TFormControl | Visual control label size for large screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| LabelSizeMedium | NUMBER | No | No | No | @(TForm).LabelSizeMedium | TFormControl | Visual control label size for medium screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| LabelSizeSmall | NUMBER | No | No | No | @(TForm).LabelSizeSmall | TFormControl | Visual control label size for small screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| MaxLength | NUMBER | No | No | No | @this.DBField.Size |  | Maximum length of the field |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| PlaceHolder | STRING | No | No | No |  | TFormControl | When control value is not specified, placeholder text will be displayed. Not supported by all controls. |
| ReadOnly | BOOLEAN | Yes | No | Yes | false | TControl | List of possible valuesfalse true |
| Required | BOOLEAN | Yes | No | Yes | false | TFormControl | Control is a required input |
| RowClass | STRING | No | No | Yes |  | TFormControl |  |
| RowStyle | STRING | No | No | Yes |  | TFormControl |  |
| Style | STRING | No | No | No |  | TControl | Style information |
| Value | STRING | No | No | Yes |  | TControl | Text value of the control |
| Visible | BOOLEAN | Yes | No | Yes | true | TControl | When true, control is visible, hidden otherwise |

### TEdit events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterCreate | GUI | TControl |  |
| onBeforeCreate | GUI | TControl |  |
| onBlur | GUI | TControl | Fired when a user leaves an input field |
| onChange | GUI | TControl | Value change event |
| onClick | GUI | TControl | onClick event |
| onConfirmFalse | GUI | TControl |  |
| onConfirmTrue | GUI | TControl |  |
| onEnterKeyPressed | GUI |  | Fires when user presses Enter key in a TEdit component |
| onHide | GUI | TControl | Fired when component transitions from shown to hidden |
| onMouseDown | GUI | TControl |  |
| onMouseOut | GUI | TControl |  |
| onMouseOver | GUI | TControl |  |
| onMouseUp | GUI | TControl |  |
| onShow | GUI | TControl | Fired when component transitions from hidden to shown |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TEdit methods

| Name | Ancestor | Description |
| --- | --- | --- |
| parseControlParameters() | TControl | Parses and executes a number of control parameters |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
| setEnabled() | TControl | Set component enabled/disabled |
| setFocus() |  | Sets focus on a form element |
| setLabel() | TControl | Sets the label text of a component |
| setReadOnly() | TControl |  |
| setRequired() | TControl |  |
| setValue() | TControl |  |
| setVisible() | TControl |  |

## TFileUpload

*Inheritance: TComponent / TControl / TFormControl / TFileUpload*

**Parent must be: TContainer**

This is a comma separated list of mime types or extensions.
E.g.: audio/*,video/*,image/png,.pdf

### TFileUpload properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| AcceptedFileTypes | STRING | Yes | No | No | image/* |  | This is a comma separated list of mime types or extensions. E.g.: audio/*,video/*,image/png,.pdf |
| Attributes | TEXT | No | No | Yes |  | TControl | HTML Special attributes |
| Class | STRING | No | No | No |  | TControl | HTML class. Used for injecting extra classes into the control. |
| ControlSizeExtraSmall | NUMBER | No | No | No | @(TForm).ControlSizeExtraSmall | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ControlSizeLarge | NUMBER | No | No | No | @(TForm).ControlSizeLarge | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ControlSizeMedium | NUMBER | No | No | No | @(TForm).ControlSizeMedium | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ControlSizeSmall | NUMBER | No | No | No | @(TForm).ControlSizeSmall | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| DBField | TDBField | No | No | Yes |  | TControl | Database field attached to the control. When specified, the control is database-aware. |
| DBParameterName | STRING | No | No | Yes |  | TControl |  |
| DBValue | STRING | No | Yes | Yes |  | TControl |  |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| Enabled | BOOLEAN | Yes | No | Yes | true | TControl | When true (default), control is enabled. Otherwise control is disabled. |
| FileProcessor | TFileProcessor | Yes | No | Yes |  |  |  |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| HelpText | TEXT | No | No | No |  | TControl | Help text to be displayed for the control |
| Icon | STRING | No | No | No |  |  | Icon specified in the icon's library format, eg. "fa fa-share" or "glyphicon glyphicon-share" |
| IconAlign | STRING | No | No | No |  |  | Alignment of the icon. Left means before the component's label or content, right means after it. Defaults to left. List of possible valuesleft right |
| Label | STRING | No | No | Yes | @this.DBField.Label | TControl | Label text of the control |
| LabelClass | STRING | No | No | No |  | TControl |  |
| LabelSizeExtraSmall | NUMBER | No | No | No | @(TForm).LabelSizeExtraSmall | TFormControl | Visual control label size for extra small screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| LabelSizeLarge | NUMBER | No | No | No | @(TForm).LabelSizeLarge | TFormControl | Visual control label size for large screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| LabelSizeMedium | NUMBER | No | No | No | @(TForm).LabelSizeMedium | TFormControl | Visual control label size for medium screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| LabelSizeSmall | NUMBER | No | No | No | @(TForm).LabelSizeSmall | TFormControl | Visual control label size for small screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| MaxFiles | NUMBER | No | No | No |  |  |  |
| MaxFileSize | NUMBER | No | No | No |  |  | Maximum uploadable file size in MB. Defaults to 25MB. |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| PlaceHolder | STRING | No | No | No | [:TFILEUPLOAD.PLACEHOLDER,Feltöltéshez kattintson vagy húzzon a szaggatott területre egy fájlt:] | TFormControl | When control value is not specified, placeholder text will be displayed. Not supported by all controls. |
| ReadOnly | BOOLEAN | Yes | No | Yes | false | TControl | List of possible valuesfalse true |
| Required | BOOLEAN | Yes | No | Yes | false | TFormControl | Control is a required input |
| RowClass | STRING | No | No | Yes |  | TFormControl |  |
| RowStyle | STRING | No | No | Yes |  | TFormControl |  |
| Style | STRING | No | No | No |  | TControl | Style information |
| ThumbnailHeight | NUMBER | No | No | No |  |  |  |
| ThumbnailWidth | NUMBER | No | No | No |  |  |  |
| UploadSender | STRING | No | Yes | Yes |  |  |  |
| UploadSenderData | STRING | No | Yes | Yes |  |  |  |
| UploadStatus | STRING | No | Yes | Yes |  |  |  |
| Value | STRING | No | No | Yes |  | TControl | Text value of the control |
| Visible | BOOLEAN | Yes | No | Yes | true | TControl | When true, control is visible, hidden otherwise |

### TFileUpload events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterCreate | GUI | TControl |  |
| onBeforeCreate | GUI | TControl |  |
| onBlur | GUI | TControl | Fired when a user leaves an input field |
| onChange | GUI | TControl | Value change event |
| onClick | GUI | TControl | onClick event |
| onConfirmFalse | GUI | TControl |  |
| onConfirmTrue | GUI | TControl |  |
| onFileSelectionError | GUI |  | File cannot be added to the upload component due to some error, eg. file too big, too many files, etc. |
| onFileUploadError | GUI |  | Fires when an error occurs during file upload |
| onHide | GUI | TControl | Fired when component transitions from shown to hidden |
| onMouseDown | GUI | TControl |  |
| onMouseOut | GUI | TControl |  |
| onMouseOver | GUI | TControl |  |
| onMouseUp | GUI | TControl |  |
| onShow | GUI | TControl | Fired when component transitions from hidden to shown |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TFileUpload methods

| Name | Ancestor | Description |
| --- | --- | --- |
| parseControlParameters() | TControl | Parses and executes a number of control parameters |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
| setEnabled() | TControl | Set component enabled/disabled |
| setLabel() | TControl | Sets the label text of a component |
| setReadOnly() | TControl |  |
| setRequired() | TControl |  |
| setValue() | TControl |  |
| setVisible() | TControl |  |

## THTMLEdit

*Inheritance: TComponent / TControl / TFormControl / THTMLEdit*

**Parent must be: TContainer**

THTMLEdit

### THTMLEdit properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Attributes | TEXT | No | No | Yes |  | TControl | HTML Special attributes |
| Class | STRING | No | No | No |  | TControl | HTML class. Used for injecting extra classes into the control. |
| ControlSizeExtraSmall | NUMBER | No | No | No | @(TForm).ControlSizeExtraSmall | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ControlSizeLarge | NUMBER | No | No | No | @(TForm).ControlSizeLarge | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ControlSizeMedium | NUMBER | No | No | No | @(TForm).ControlSizeMedium | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ControlSizeSmall | NUMBER | No | No | No | @(TForm).ControlSizeSmall | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| DBField | TDBField | No | No | Yes |  | TControl | Database field attached to the control. When specified, the control is database-aware. |
| DBParameterName | STRING | No | No | Yes |  | TControl |  |
| DBValue | STRING | No | Yes | No |  | TControl |  |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| Enabled | BOOLEAN | Yes | No | Yes | true | TControl | When true (default), control is enabled. Otherwise control is disabled. |
| FunctionCode | STRING | No | No | Yes |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| HelpText | TEXT | No | No | No |  | TControl | Help text to be displayed for the control |
| HTMLEditorType | STRING | Yes | No | No | advanced |  | Type of the HTML editor. Simple offers basic features, advanced offers more control over editing the text. List of possible valuessimple advanced |
| Icon | STRING | No | No | No |  |  | Icon specified in the icon's library format, eg. "fa fa-share" or "glyphicon glyphicon-share" |
| IconAlign | STRING | No | No | No |  |  | Alignment of the icon. Left means before the component's label or content, right means after it. Defaults to left. List of possible valuesleft right |
| Label | STRING | No | No | No | @this.DBField.Label | TControl | Label text of the control |
| LabelClass | STRING | No | No | No |  | TControl |  |
| LabelSizeExtraSmall | NUMBER | No | No | No | @(TForm).LabelSizeExtraSmall | TFormControl | Visual control label size for extra small screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| LabelSizeLarge | NUMBER | No | No | No | @(TForm).LabelSizeLarge | TFormControl | Visual control label size for large screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| LabelSizeMedium | NUMBER | No | No | No | @(TForm).LabelSizeMedium | TFormControl | Visual control label size for medium screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| LabelSizeSmall | NUMBER | No | No | No | @(TForm).LabelSizeSmall | TFormControl | Visual control label size for small screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| PlaceHolder | STRING | No | No | No |  | TFormControl | When control value is not specified, placeholder text will be displayed. Not supported by all controls. |
| ReadOnly | BOOLEAN | Yes | No | Yes | false | TControl | List of possible valuesfalse true |
| Required | BOOLEAN | Yes | No | Yes | false | TFormControl | Control is a required input |
| RowClass | STRING | No | No | Yes |  | TFormControl |  |
| RowStyle | STRING | No | No | Yes |  | TFormControl |  |
| Style | STRING | No | No | No |  | TControl | Style information |
| Value | STRING | No | No | No |  | TControl | Text value of the control |
| Visible | BOOLEAN | Yes | No | Yes | true | TControl | When true, control is visible, hidden otherwise |

### THTMLEdit events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterCreate | GUI | TControl |  |
| onBeforeCreate | GUI | TControl |  |
| onBlur | GUI | TControl | Fired when a user leaves an input field |
| onChange | GUI | TControl | Value change event |
| onClick | GUI | TControl | onClick event |
| onConfirmFalse | GUI | TControl |  |
| onConfirmTrue | GUI | TControl |  |
| onHide | GUI | TControl | Fired when component transitions from shown to hidden |
| onMouseDown | GUI | TControl |  |
| onMouseOut | GUI | TControl |  |
| onMouseOver | GUI | TControl |  |
| onMouseUp | GUI | TControl |  |
| onShow | GUI | TControl | Fired when component transitions from hidden to shown |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### THTMLEdit methods

| Name | Ancestor | Description |
| --- | --- | --- |
| parseControlParameters() | TControl | Parses and executes a number of control parameters |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
| setEnabled() | TControl | Set component enabled/disabled |
| setLabel() | TControl | Sets the label text of a component |
| setReadOnly() | TControl |  |
| setRequired() | TControl |  |
| setValue() | TControl |  |
| setVisible() | TControl |  |

## TLabel

*Inheritance: TComponent / TControl / TFormControl / TLabel*

**Parent must be: TComponent**

TLabel

### TLabel properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Attributes | TEXT | No | No | Yes |  | TControl | HTML Special attributes |
| Class | STRING | No | No | No |  | TControl | HTML class. Used for injecting extra classes into the control. |
| DBField | TDBField | No | No | Yes |  | TControl | Database field attached to the control. When specified, the control is database-aware. |
| DBParameterName | STRING | No | No | Yes |  | TControl |  |
| DBValue | STRING | No | Yes | Yes |  | TControl |  |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| Enabled | BOOLEAN | Yes | No | Yes | true | TControl | When true (default), control is enabled. Otherwise control is disabled. |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| HelpText | TEXT | No | No | No |  | TControl | Help text to be displayed for the control |
| Icon | STRING | No | No | No |  |  | Icon specified in the icon's library format, eg. "fa fa-share" or "glyphicon glyphicon-share" |
| IconAlign | STRING | No | No | No |  |  | Alignment of the icon. Left means before the component's label or content, right means after it. Defaults to left. List of possible valuesleft right |
| Label | STRING | No | No | No | @this.DBField.Label | TControl | Label text of the control |
| LabelClass | STRING | No | No | No |  | TControl |  |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| RowClass | STRING | No | No | Yes |  | TFormControl |  |
| RowStyle | STRING | No | No | Yes |  | TFormControl |  |
| Style | STRING | No | No | No |  | TControl | Style information |
| Typography | STRING | Yes | No | No | p |  | Text type eg. headings, paragraph, block quote, etc. List of possible valuesp span h1 h2 h3 h4 h5 h6 blockquote footer var samp |
| Value | STRING | No | No | Yes |  | TControl | Text value of the control |
| Visible | BOOLEAN | Yes | No | Yes | true | TControl | When true, control is visible, hidden otherwise |

### TLabel events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterCreate | GUI | TControl |  |
| onBeforeCreate | GUI | TControl |  |
| onBlur | GUI | TControl | Fired when a user leaves an input field |
| onChange | GUI | TControl | Value change event |
| onClick | GUI | TControl | onClick event |
| onConfirmFalse | GUI | TControl |  |
| onConfirmTrue | GUI | TControl |  |
| onHide | GUI | TControl | Fired when component transitions from shown to hidden |
| onMouseDown | GUI | TControl |  |
| onMouseOut | GUI | TControl |  |
| onMouseOver | GUI | TControl |  |
| onMouseUp | GUI | TControl |  |
| onShow | GUI | TControl | Fired when component transitions from hidden to shown |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TLabel methods

| Name | Ancestor | Description |
| --- | --- | --- |
| parseControlParameters() | TControl | Parses and executes a number of control parameters |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
| setEnabled() | TControl | Set component enabled/disabled |
| setLabel() | TControl | Sets the label text of a component |
| setReadOnly() | TControl |  |
| setRequired() | TControl |  |
| setValue() | TControl |  |
| setVisible() | TControl |  |

## TLOV

*Inheritance: TComponent / TControl / TFormControl / TLOV*

**Parent must be: TContainer**

TLOV

### TLOV properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| AJAXMode | BOOLEAN | Yes | No | Yes | false |  | Fetches associated list at render time (false) or loading time via AJAX (true) |
| AJAXQueueID | STRING | Yes | No | Yes | @this.Name |  |  |
| Attributes | TEXT | No | No | Yes |  | TControl | HTML Special attributes |
| Class | STRING | No | No | No |  | TControl | HTML class. Used for injecting extra classes into the control. |
| ControlSizeExtraSmall | NUMBER | No | No | No | @(TForm).ControlSizeExtraSmall | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ControlSizeLarge | NUMBER | No | No | No | @(TForm).ControlSizeLarge | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ControlSizeMedium | NUMBER | No | No | No | @(TForm).ControlSizeMedium | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ControlSizeSmall | NUMBER | No | No | No | @(TForm).ControlSizeSmall | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| DBField | TDBField | No | No | Yes |  | TControl | Database field attached to the control. When specified, the control is database-aware. |
| DBParameterName | STRING | No | No | Yes |  | TControl |  |
| DBValue | STRING | No | Yes | Yes |  | TControl |  |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| Diacritics | BOOLEAN | Yes | No | No | true |  | Pl. Aéró-t megtalálja aero-ra is |
| Enabled | BOOLEAN | Yes | No | Yes | true | TControl | When true (default), control is enabled. Otherwise control is disabled. |
| FieldId | STRING | Yes | No | Yes | id |  |  |
| FieldText | STRING | Yes | No | Yes | text |  |  |
| ForcedRefresh | BOOLEAN | Yes | No | Yes | false |  | Skip intelligent refresh check on GUI |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| HelpText | TEXT | No | No | No |  | TControl | Help text to be displayed for the control |
| Icon | STRING | No | No | No |  |  | Icon specified in the icon's library format, eg. "fa fa-share" or "glyphicon glyphicon-share" |
| IconAlign | STRING | No | No | No |  |  | Alignment of the icon. Left means before the component's label or content, right means after it. Defaults to left. List of possible valuesleft right |
| Label | STRING | No | No | No | @this.DBField.Label | TControl | Label text of the control |
| LabelClass | STRING | No | No | No |  | TControl |  |
| LabelSizeExtraSmall | NUMBER | No | No | No | @(TForm).LabelSizeExtraSmall | TFormControl | Visual control label size for extra small screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| LabelSizeLarge | NUMBER | No | No | No | @(TForm).LabelSizeLarge | TFormControl | Visual control label size for large screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| LabelSizeMedium | NUMBER | No | No | No | @(TForm).LabelSizeMedium | TFormControl | Visual control label size for medium screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| LabelSizeSmall | NUMBER | No | No | No | @(TForm).LabelSizeSmall | TFormControl | Visual control label size for small screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ListFilter | STRING | No | No | Yes |  |  | Source data filtering, URL format |
| ListSource | TQuery | Yes | No | Yes |  |  | JSON data provider that provides JSON-formatted data to the component |
| Master | TControl | No | No | Yes |  |  | Used in dependant control situations. Specifies the master component which this control depends on. When master component refreshes its value, this component will also be refreshed. |
| MasterFilterField | STRING | No | No | Yes |  |  |  |
| MaxSelLength | NUMBER | No | No | No |  |  | Only works with MultiSelect on. It limits the number of items can be selected in a dropdown list. Defaults to no limit. |
| MiddleClick | BOOLEAN | No | No | Yes |  |  | Enable middle click support on component's click event. |
| MinimumSearchLength | NUMBER | Yes | No | Yes | 1 |  |  |
| MultiSelect | BOOLEAN | Yes | No | Yes | false |  | When true, multiple entries can be selected in a dropdown list. When false, only a single entry is accepted. |
| Name | STRING | No | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| PlaceHolder | STRING | No | No | No |  | TFormControl | When control value is not specified, placeholder text will be displayed. Not supported by all controls. |
| ReadOnly | BOOLEAN | Yes | No | Yes | false | TControl | List of possible valuesfalse true |
| Required | BOOLEAN | Yes | No | Yes | false | TFormControl | Control is a required input |
| RowClass | STRING | No | No | Yes |  | TFormControl |  |
| RowStyle | STRING | No | No | Yes |  | TFormControl |  |
| Searchable | BOOLEAN | Yes | No | Yes | true |  | When true, search box is displayed on top of the dropdown. Defaults to true. |
| ServerSideSearch | BOOLEAN | Yes | No | Yes | false |  |  |
| ShowRefresh | BOOLEAN | Yes | No | No | false |  | Show Refresh button on component. Defaults to false. |
| Style | STRING | No | No | No |  | TControl | Style information |
| Value | STRING | No | No | Yes |  | TControl | Text value of the control |
| Visible | BOOLEAN | Yes | No | Yes | true | TControl | When true, control is visible, hidden otherwise |

### TLOV events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterCreate | GUI | TControl |  |
| onAfterRefresh | GUI |  | After control was refreshed successfully or unsuccessfully |
| onBeforeCreate | GUI | TControl |  |
| onBlur | GUI | TControl | Fired when a user leaves an input field |
| onChange | GUI | TControl | Value change event |
| onClick | GUI | TControl | onClick event |
| onConfirmFalse | GUI | TControl |  |
| onConfirmTrue | GUI | TControl |  |
| onHide | GUI | TControl | Fired when component transitions from shown to hidden |
| onMouseDown | GUI | TControl |  |
| onMouseOut | GUI | TControl |  |
| onMouseOver | GUI | TControl |  |
| onMouseUp | GUI | TControl |  |
| onShow | GUI | TControl | Fired when component transitions from hidden to shown |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TLOV methods

| Name | Ancestor | Description |
| --- | --- | --- |
| parseControlParameters() | TControl | Parses and executes a number of control parameters |
| refresh() |  | Refreshes component data |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
| setEnabled() | TControl | Set component enabled/disabled |
| setLabel() | TControl | Sets the label text of a component |
| setReadOnly() | TControl |  |
| setRequired() | TControl |  |
| setValue() | TControl |  |
| setVisible() | TControl |  |

## TRadio

*Inheritance: TComponent / TControl / TFormControl / TRadio*

**Parent must be: TContainer**

TRadio

### TRadio properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Attributes | TEXT | No | No | Yes |  | TControl | HTML Special attributes |
| Class | STRING | No | No | No |  | TControl | HTML class. Used for injecting extra classes into the control. |
| ControlSizeExtraSmall | NUMBER | No | No | No | @(TForm).ControlSizeExtraSmall | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ControlSizeLarge | NUMBER | No | No | No | @(TForm).ControlSizeLarge | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ControlSizeMedium | NUMBER | No | No | No | @(TForm).ControlSizeMedium | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ControlSizeSmall | NUMBER | No | No | No | @(TForm).ControlSizeSmall | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| DBField | TDBField | No | No | Yes |  | TControl | Database field attached to the control. When specified, the control is database-aware. |
| DBParameterName | STRING | No | No | Yes |  | TControl |  |
| DBValue | STRING | No | Yes | Yes |  | TControl |  |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| Enabled | BOOLEAN | Yes | No | Yes | true | TControl | When true (default), control is enabled. Otherwise control is disabled. |
| FieldId | STRING | Yes | No | Yes | id |  |  |
| FieldText | STRING | Yes | No | Yes | text |  |  |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| HelpText | TEXT | No | No | No |  | TControl | Help text to be displayed for the control |
| Icon | STRING | No | No | No |  |  | Icon specified in the icon's library format, eg. "fa fa-share" or "glyphicon glyphicon-share" |
| IconAlign | STRING | No | No | No |  |  | Alignment of the icon. Left means before the component's label or content, right means after it. Defaults to left. List of possible valuesleft right |
| Label | STRING | No | No | No |  | TControl | Label text of the control |
| LabelClass | STRING | No | No | No |  | TControl |  |
| LabelSizeExtraSmall | NUMBER | No | No | No | @(TForm).LabelSizeExtraSmall | TFormControl | Visual control label size for extra small screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| LabelSizeLarge | NUMBER | No | No | No | @(TForm).LabelSizeLarge | TFormControl | Visual control label size for large screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| LabelSizeMedium | NUMBER | No | No | No | @(TForm).LabelSizeMedium | TFormControl | Visual control label size for medium screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| LabelSizeSmall | NUMBER | No | No | No | @(TForm).LabelSizeSmall | TFormControl | Visual control label size for small screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ListFilter | STRING | No | No | Yes |  |  | Source data filtering, URL format |
| ListSource | TQuery | Yes | No | Yes |  |  | JSON data provider that provides JSON-formatted data to the component |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| RadioInline | BOOLEAN | Yes | No | No | false |  | List of possible valuestrue false |
| ReadOnly | BOOLEAN | Yes | No | Yes | false | TControl | List of possible valuesfalse true |
| Required | BOOLEAN | Yes | No | Yes | true | TFormControl | Control is a required input |
| RowClass | STRING | No | No | Yes |  | TFormControl |  |
| RowStyle | STRING | No | No | Yes |  | TFormControl |  |
| Style | STRING | No | No | No |  | TControl | Style information |
| Value | STRING | No | No | Yes |  | TControl | Text value of the control |
| Visible | BOOLEAN | No | No | Yes |  | TControl | When true, control is visible, hidden otherwise |

### TRadio events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterCreate | GUI | TControl |  |
| onBeforeCreate | GUI | TControl |  |
| onBlur | GUI | TControl | Fired when a user leaves an input field |
| onChange | GUI | TControl | Value change event |
| onClick | GUI | TControl | onClick event |
| onConfirmFalse | GUI | TControl |  |
| onConfirmTrue | GUI | TControl |  |
| onHide | GUI | TControl | Fired when component transitions from shown to hidden |
| onMouseDown | GUI | TControl |  |
| onMouseOut | GUI | TControl |  |
| onMouseOver | GUI | TControl |  |
| onMouseUp | GUI | TControl |  |
| onShow | GUI | TControl | Fired when component transitions from hidden to shown |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TRadio methods

| Name | Ancestor | Description |
| --- | --- | --- |
| parseControlParameters() | TControl | Parses and executes a number of control parameters |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
| setEnabled() | TControl | Set component enabled/disabled |
| setLabel() | TControl | Sets the label text of a component |
| setReadOnly() | TControl |  |
| setRequired() | TControl |  |
| setValue() | TControl |  |
| setVisible() | TControl |  |

## TStatic

*Inheritance: TComponent / TControl / TFormControl / TStatic*

**Parent must be: TContainer**

TStatic

### TStatic properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Attributes | TEXT | No | No | Yes |  | TControl | HTML Special attributes |
| Class | STRING | No | No | No |  | TControl | HTML class. Used for injecting extra classes into the control. |
| ControlSizeExtraSmall | NUMBER | No | No | No | @(TForm).ControlSizeExtraSmall | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ControlSizeLarge | NUMBER | No | No | No | @(TForm).ControlSizeLarge | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ControlSizeMedium | NUMBER | No | No | No | @(TForm).ControlSizeMedium | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ControlSizeSmall | NUMBER | No | No | No | @(TForm).ControlSizeSmall | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| DBField | TDBField | No | No | Yes |  | TControl | Database field attached to the control. When specified, the control is database-aware. |
| DBParameterName | STRING | No | No | Yes |  | TControl |  |
| DBValue | STRING | No | Yes | Yes |  | TControl |  |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| Enabled | BOOLEAN | Yes | No | Yes | true | TControl | When true (default), control is enabled. Otherwise control is disabled. |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| HelpText | TEXT | No | No | No |  | TControl | Help text to be displayed for the control |
| Icon | STRING | No | No | No |  |  | Icon specified in the icon's library format, eg. "fa fa-share" or "glyphicon glyphicon-share" |
| IconAlign | STRING | No | No | No |  |  | Alignment of the icon. Left means before the component's label or content, right means after it. Defaults to left. List of possible valuesleft right |
| Label | STRING | No | No | No | @this.DBField.Label | TControl | Label text of the control |
| LabelClass | STRING | No | No | No |  | TControl |  |
| LabelSizeExtraSmall | NUMBER | No | No | No |  | TFormControl | Visual control label size for extra small screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| LabelSizeLarge | NUMBER | No | No | No |  | TFormControl | Visual control label size for large screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| LabelSizeMedium | NUMBER | No | No | No | @(TForm).LabelSizeMedium | TFormControl | Visual control label size for medium screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| LabelSizeSmall | NUMBER | No | No | No |  | TFormControl | Visual control label size for small screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| RowClass | STRING | No | No | Yes |  | TFormControl |  |
| RowStyle | STRING | No | No | Yes |  | TFormControl |  |
| Style | STRING | No | No | No |  | TControl | Style information |
| Value | STRING | No | No | Yes |  | TControl | Text value of the control |
| Visible | BOOLEAN | Yes | No | Yes | true | TControl | When true, control is visible, hidden otherwise |

### TStatic events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterCreate | GUI | TControl |  |
| onBeforeCreate | GUI | TControl |  |
| onBlur | GUI | TControl | Fired when a user leaves an input field |
| onChange | GUI | TControl | Value change event |
| onClick | GUI | TControl | onClick event |
| onConfirmFalse | GUI | TControl |  |
| onConfirmTrue | GUI | TControl |  |
| onHide | GUI | TControl | Fired when component transitions from shown to hidden |
| onMouseDown | GUI | TControl |  |
| onMouseOut | GUI | TControl |  |
| onMouseOver | GUI | TControl |  |
| onMouseUp | GUI | TControl |  |
| onShow | GUI | TControl | Fired when component transitions from hidden to shown |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TStatic methods

| Name | Ancestor | Description |
| --- | --- | --- |
| parseControlParameters() | TControl | Parses and executes a number of control parameters |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
| setEnabled() | TControl | Set component enabled/disabled |
| setLabel() | TControl | Sets the label text of a component |
| setReadOnly() | TControl |  |
| setRequired() | TControl |  |
| setValue() | TControl |  |
| setVisible() | TControl |  |

## TText

*Inheritance: TComponent / TControl / TFormControl / TText*

**Parent must be: TContainer**

TText

### TText properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Attributes | TEXT | No | No | Yes |  | TControl | HTML Special attributes |
| Autosize | BOOLEAN | Yes | No | No | false |  | Autosize rows with animation as user in entering new rows |
| Class | STRING | No | No | No |  | TControl | HTML class. Used for injecting extra classes into the control. |
| Cols | NUMBER | No | No | No |  |  |  |
| ControlSizeExtraSmall | NUMBER | No | No | No | @(TForm).ControlSizeExtraSmall | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ControlSizeLarge | NUMBER | No | No | No | @(TForm).ControlSizeLarge | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ControlSizeMedium | NUMBER | No | No | No | @(TForm).ControlSizeMedium | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| ControlSizeSmall | NUMBER | No | No | No | @(TForm).ControlSizeSmall | TFormControl | List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| DBField | TDBField | No | No | Yes |  | TControl | Database field attached to the control. When specified, the control is database-aware. |
| DBParameterName | STRING | No | No | Yes |  | TControl |  |
| DBValue | STRING | No | Yes | No |  | TControl |  |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| Enabled | BOOLEAN | Yes | No | Yes | true | TControl | When true (default), control is enabled. Otherwise control is disabled. |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| HelpText | TEXT | No | No | No |  | TControl | Help text to be displayed for the control |
| Icon | STRING | No | No | No |  |  | Icon specified in the icon's library format, eg. "fa fa-share" or "glyphicon glyphicon-share" |
| IconAlign | STRING | No | No | No |  |  | Alignment of the icon. Left means before the component's label or content, right means after it. Defaults to left. List of possible valuesleft right |
| Label | STRING | No | No | No | @this.DBField.Label | TControl | Label text of the control |
| LabelClass | STRING | No | No | No |  | TControl |  |
| LabelSizeExtraSmall | NUMBER | No | No | No | @(TForm).LabelSizeExtraSmall | TFormControl | Visual control label size for extra small screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| LabelSizeLarge | NUMBER | No | No | No | @(TForm).LabelSizeLarge | TFormControl | Visual control label size for large screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| LabelSizeMedium | NUMBER | No | No | No | @(TForm).LabelSizeMedium | TFormControl | Visual control label size for medium screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| LabelSizeSmall | NUMBER | No | No | No | @(TForm).LabelSizeSmall | TFormControl | Visual control label size for small screens. List of possible values1 2 3 4 5 6 7 8 9 10 11 12 |
| MaxLength | NUMBER | No | No | No | @this.DBField.Size |  | Maximum length of the field |
| Name | STRING | Yes | No | Yes | false | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| PlaceHolder | STRING | No | No | No |  | TFormControl | When control value is not specified, placeholder text will be displayed. Not supported by all controls. |
| ReadOnly | BOOLEAN | Yes | No | Yes | false | TControl | List of possible valuesfalse true |
| Required | BOOLEAN | Yes | No | Yes | false | TFormControl | Control is a required input |
| RowClass | STRING | No | No | Yes |  | TFormControl |  |
| Rows | NUMBER | No | No | No |  |  | Number of rows to be displayed |
| RowStyle | STRING | No | No | Yes |  | TFormControl |  |
| Style | STRING | No | No | No |  | TControl | Style information |
| Value | STRING | No | No | Yes |  | TControl | Text value of the control |
| Visible | BOOLEAN | Yes | No | Yes | true | TControl | When true, control is visible, hidden otherwise |

### TText events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterCreate | GUI | TControl |  |
| onBeforeCreate | GUI | TControl |  |
| onBlur | GUI | TControl | Fired when a user leaves an input field |
| onChange | GUI | TControl | Value change event |
| onClick | GUI | TControl | onClick event |
| onConfirmFalse | GUI | TControl |  |
| onConfirmTrue | GUI | TControl |  |
| onHide | GUI | TControl | Fired when component transitions from shown to hidden |
| onMouseDown | GUI | TControl |  |
| onMouseOut | GUI | TControl |  |
| onMouseOver | GUI | TControl |  |
| onMouseUp | GUI | TControl |  |
| onShow | GUI | TControl | Fired when component transitions from hidden to shown |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TText methods

| Name | Ancestor | Description |
| --- | --- | --- |
| parseControlParameters() | TControl | Parses and executes a number of control parameters |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
| setEnabled() | TControl | Set component enabled/disabled |
| setLabel() | TControl | Sets the label text of a component |
| setReadOnly() | TControl |  |
| setRequired() | TControl |  |
| setValue() | TControl |  |
| setVisible() | TControl |  |

## TGrid

*Inheritance: TComponent / TControl / TGrid*

**Parent must be: TContainer**

TGrid

### TGrid properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| ActivePage | NUMBER | No | Yes | No | 1 |  |  |
| AJAXMode | BOOLEAN | Yes | No | Yes | true |  | Fetches associated list at render time (false) or loading time via AJAX (true) |
| AJAXQueueID | STRING | Yes | No | Yes | @this.Name |  |  |
| Attributes | TEXT | No | No | Yes |  | TControl | HTML Special attributes |
| AutoFilterRefresh | BOOLEAN | Yes | No | Yes | true |  | Automatically refreshes the grid on filter change |
| AutoLoad | BOOLEAN | Yes | No | Yes | true |  | Loads data on render time. If false, manual refresh needed on client side. |
| CacheValidity | NUMBER | No | No | No |  |  | Cache validity in minutes |
| Caching | BOOLEAN | Yes | No | No | false |  | Disable or enable list source caching |
| ChartOptions | JSON | No | No | No | {} |  | ChartJS options |
| ChartXAxis | TDBField | No | No | No |  |  | X Axis of chart |
| Class | STRING | No | No | No |  | TControl | HTML class. Used for injecting extra classes into the control. |
| ContainerClass | STRING | No | No | No |  |  |  |
| DataContainerClass | STRING | No | No | No |  |  |  |
| DataGenerated | BOOLEAN | No | Yes | Yes |  |  | Data is generated - in case of AJAX mode the value of this property is false to indicate to the JS side to refresh the grid's data |
| DBField | TDBField | No | No | Yes |  | TControl | Database field attached to the control. When specified, the control is database-aware. |
| DBParameterName | STRING | No | No | Yes |  | TControl |  |
| DBValue | STRING | No | Yes | Yes |  | TControl |  |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| Enabled | BOOLEAN | Yes | No | Yes | true | TControl | When true (default), control is enabled. Otherwise control is disabled. |
| Exportable | BOOLEAN | Yes | No | No | true |  | This control can be exported eg. to Excel |
| ExportFunctionCode | STRING | No | No | No | GRID.EXPORT |  | Function code for exporting |
| FilterSQL | STRING | No | Yes | No |  |  | Generated SQL filter sentence |
| FilterTemplate | TEMPLATE | No | No | No |  |  | Hard-coded fixed filters to appear above the grid |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| GridHTMLType | STRING | Yes | No | Yes | table |  | List of possible valuestable div div-responsive |
| HelpText | TEXT | No | No | Yes |  | TControl | Help text to be displayed for the control |
| LabelCacheInfo | STRING | Yes | No | Yes | [:GRID.CACHEINFO,Utoljára frissítve::] |  |  |
| LabelFilterButton | STRING | Yes | No | No | [:GRID.HEADER.FILTERS,Szűrők:] |  |  |
| LabelNoDataGenerated | STRING | Yes | No | No | [:GRID.HEADER.NO_DATA_GENERATED,Az adatok megjelenítéséhez frissíteni kell!:] |  | Label if no data generated to the grid (in case of first render when AutoLoad is false ) |
| LabelNoFilter | STRING | Yes | No | No | [:GRID.HEADER.NOFILTERS,Nincsenek szűrők beállítva:] |  |  |
| LabelRefresh | STRING | Yes | No | No | [:GRID.BUTTON.REFRESH,Frissítés:] |  |  |
| LabelSelectionOutOfList | STRING | Yes | No | No | [:GRID.HEADER.SELECTION,A kiválasztott tétel nem látszik:] |  |  |
| ListSource | TQuery | Yes | No | No |  |  | JSON data provider that provides JSON-formatted data to the component |
| ListsourceAlwaysReopen | BOOLEAN | Yes | No | Yes | false |  | In case multiple grids use a single listsource, reopen must be forced because of internal cache handling |
| LookupValue | STRING | No | No | Yes | @this.DBField.Value |  | Select row's value |
| Master | TControl | No | No | Yes |  |  | Used in dependant control situations. Specifies the master component which this control depends on. When master component refreshes its value, this component will also be refreshed. |
| MasterDBField | TDBField | No | No | No |  |  |  |
| MasterValue | STRING | No | Yes | No |  |  |  |
| MultiSelect | BOOLEAN | Yes | No | Yes | false |  | When true, multiple entries can be selected in a dropdown list. When false, only a single entry is accepted. |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| PageCount | NUMBER | No | Yes | No |  |  |  |
| PersistencyPrefix | STRING | No | No | No |  |  |  |
| Persistent | STRING | No | No | Yes |  |  | List of possible valuesDATABASE SESSION |
| PublishFilterSQL | BOOLEAN | Yes | No | No | false |  | Publish filter SQL to page |
| RefreshWhenHidden | BOOLEAN | Yes | No | Yes | true |  | Refresh grid even if it is hidden |
| RowClass | STRING | No | No | No |  |  |  |
| RowCount | NUMBER | No | Yes | No |  |  |  |
| RowsPerPage | NUMBER | Yes | No | No | 0 |  | Maximum rows per page for pagination. Defaults to 10. |
| RowsPerPageList | STRING | Yes | No | No | 10,20,50,100,200 |  | Show these options in the pagination area |
| RowStyle | STRING | No | No | No |  |  |  |
| Scrollable | BOOLEAN | Yes | No | No | @(parameter).Tholos.TGrid.Scrollable\|false |  |  |
| Selectable | BOOLEAN | Yes | No | Yes | false |  |  |
| ShowCacheInfo | BOOLEAN | Yes | No | Yes | true |  |  |
| ShowExportButton | BOOLEAN | Yes | No | Yes | true |  | Show export button |
| ShowRefreshButton | BOOLEAN | Yes | No | Yes | true |  | Show or hide refresh button |
| ShowScrollCheckbox | BOOLEAN | Yes | No | Yes | true |  | Hide or show scroll checkbox |
| ShowTransposeCheckbox | BOOLEAN | Yes | No | Yes | true |  | Show transpose checkbox |
| SortedBy | TGridColumn | No | No | No |  |  |  |
| SortedByAlways | STRING | No | No | No |  |  | If set column headers sorting option is disabled and sends this to orderby |
| SortingDirection | STRING | No | Yes | No |  |  | List of possible valuesASC DESC |
| Style | STRING | No | No | No |  | TControl | Style information |
| Title | STRING | No | No | No |  |  |  |
| TotalRowCount | NUMBER | No | Yes | No |  |  |  |
| Transposed | BOOLEAN | Yes | No | Yes | false |  | Grid is transposed |
| UUID | STRING | No | Yes | Yes |  |  | Unique ID |
| Value | STRING | No | Yes | Yes |  | TControl | Text value of the control |
| ViewModeInit | STRING | Yes | No | Yes | GRID |  | Default view mode of grid List of possible valuesGRID CHART |
| Visible | BOOLEAN | Yes | No | Yes | true | TControl | When true, control is visible, hidden otherwise |

### TGrid events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterCreate | GUI | TControl |  |
| onAfterRefresh | GUI |  | After control was refreshed successfully or unsuccessfully |
| onBeforeCreate | GUI | TControl |  |
| onBlur | GUI | TControl | Fired when a user leaves an input field |
| onChange | GUI | TControl | Value change event |
| onClick | GUI | TControl | onClick event |
| onConfirmFalse | GUI | TControl |  |
| onConfirmTrue | GUI | TControl |  |
| onHide | GUI | TControl | Fired when component transitions from shown to hidden |
| onMouseDown | GUI | TControl |  |
| onMouseOut | GUI | TControl |  |
| onMouseOver | GUI | TControl |  |
| onMouseUp | GUI | TControl |  |
| onSelectionChange | GUI |  | Fired when selection changed in Multiselect mode |
| onShow | GUI | TControl | Fired when component transitions from hidden to shown |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TGrid methods

| Name | Ancestor | Description |
| --- | --- | --- |
| downloadExcel() |  | Download grid records in Excel format |
| getValues() |  | If component is multiselectable, getValues gives back the selected values in JSON array |
| initialize() |  | First time refresh. Runs only one time. |
| parseControlParameters() | TControl | Parses and executes a number of control parameters |
| refresh() |  | Refreshes component data |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
| setEnabled() | TControl | Set component enabled/disabled |
| setLabel() | TControl | Sets the label text of a component |
| setReadOnly() | TControl |  |
| setRequired() | TControl |  |
| setValue() | TControl |  |
| setValueRefresh() |  | Sets value and refresh |
| setVisible() | TControl |  |
| showDetails() |  |  |

## THidden

*Inheritance: TComponent / TControl / THidden*

**Parent must be: TContainer**

THidden

### THidden properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Attributes | TEXT | No | No | Yes |  | TControl | HTML Special attributes |
| DBField | TDBField | No | No | Yes |  | TControl | Database field attached to the control. When specified, the control is database-aware. |
| DBParameterName | STRING | No | No | Yes |  | TControl |  |
| DBValue | STRING | No | Yes | Yes |  | TControl |  |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| Value | STRING | No | No | Yes |  | TControl | Text value of the control |

### THidden events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterCreate | GUI | TControl |  |
| onBeforeCreate | GUI | TControl |  |
| onBlur | GUI | TControl | Fired when a user leaves an input field |
| onChange | GUI | TControl | Value change event |
| onClick | GUI | TControl | onClick event |
| onConfirmFalse | GUI | TControl |  |
| onConfirmTrue | GUI | TControl |  |
| onHide | GUI | TControl | Fired when component transitions from shown to hidden |
| onMouseDown | GUI | TControl |  |
| onMouseOut | GUI | TControl |  |
| onMouseOver | GUI | TControl |  |
| onMouseUp | GUI | TControl |  |
| onShow | GUI | TControl | Fired when component transitions from hidden to shown |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### THidden methods

| Name | Ancestor | Description |
| --- | --- | --- |
| parseControlParameters() | TControl | Parses and executes a number of control parameters |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
| setEnabled() | TControl | Set component enabled/disabled |
| setLabel() | TControl | Sets the label text of a component |
| setReadOnly() | TControl |  |
| setRequired() | TControl |  |
| setValue() | TControl |  |
| setVisible() | TControl |  |

## TImage

*Inheritance: TComponent / TControl / TImage*

**Parent must be: TContainer**

TImage

### TImage properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| AlternateText | STRING | No | No | No |  |  | Alternate text for the HTML image |
| Attributes | TEXT | No | No | Yes |  | TControl | HTML Special attributes |
| Class | STRING | No | No | No |  | TControl | HTML class. Used for injecting extra classes into the control. |
| DBField | TDBField | No | No | Yes |  | TControl | Database field attached to the control. When specified, the control is database-aware. |
| DBParameterName | STRING | No | No | Yes |  | TControl |  |
| DBValue | STRING | No | Yes | Yes |  | TControl |  |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| HelpText | TEXT | No | No | No |  | TControl | Help text to be displayed for the control |
| ImageHeight | NUMBER | No | No | No |  |  | Height of the image |
| ImageWidth | NUMBER | No | No | No |  |  | Width of the image |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| Responsive | BOOLEAN | Yes | No | No | true |  | Images can be made responsive-friendly by using this flag. This applies max-width: 100%;, height: auto; and display: block; to the image so that it scales nicely to the parent element. |
| Style | STRING | No | No | No |  | TControl | Style information |
| Value | STRING | No | No | Yes |  | TControl | Text value of the control |
| Visible | BOOLEAN | Yes | No | Yes | true | TControl | When true, control is visible, hidden otherwise |

### TImage events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterCreate | GUI | TControl |  |
| onBeforeCreate | GUI | TControl |  |
| onBlur | GUI | TControl | Fired when a user leaves an input field |
| onChange | GUI | TControl | Value change event |
| onClick | GUI | TControl | onClick event |
| onConfirmFalse | GUI | TControl |  |
| onConfirmTrue | GUI | TControl |  |
| onHide | GUI | TControl | Fired when component transitions from shown to hidden |
| onMouseDown | GUI | TControl |  |
| onMouseOut | GUI | TControl |  |
| onMouseOver | GUI | TControl |  |
| onMouseUp | GUI | TControl |  |
| onShow | GUI | TControl | Fired when component transitions from hidden to shown |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TImage methods

| Name | Ancestor | Description |
| --- | --- | --- |
| parseControlParameters() | TControl | Parses and executes a number of control parameters |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
| setEnabled() | TControl | Set component enabled/disabled |
| setLabel() | TControl | Sets the label text of a component |
| setReadOnly() | TControl |  |
| setRequired() | TControl |  |
| setValue() | TControl |  |
| setVisible() | TControl |  |

## TLink

*Inheritance: TComponent / TControl / TLink*

**Parent must be: TContainer**

TLink

### TLink properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Action | TAction | No | No | Yes |  |  | Action that this component runs |
| Attributes | TEXT | No | No | Yes |  | TControl | HTML Special attributes |
| Class | STRING | No | No | No |  | TControl | HTML class. Used for injecting extra classes into the control. |
| ConfirmDialog | TConfirmDialog | No | No | Yes |  |  |  |
| DBField | TDBField | No | No | Yes |  | TControl | Database field attached to the control. When specified, the control is database-aware. |
| DBParameterName | STRING | No | No | Yes |  | TControl |  |
| DBValue | STRING | No | Yes | Yes |  | TControl |  |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FunctionCode | STRING | Yes | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| HelpText | TEXT | No | No | No |  | TControl | Help text to be displayed for the control |
| Icon | STRING | No | No | No |  |  | Icon specified in the icon's library format, eg. "fa fa-share" or "glyphicon glyphicon-share" |
| IconAlign | STRING | No | No | No | left |  | Alignment of the icon. Left means before the component's label or content, right means after it. Defaults to left. List of possible valuesleft right |
| Label | STRING | Yes | No | No |  | TControl | Label text of the control |
| LabelClass | STRING | No | No | No |  | TControl |  |
| LinkTarget | STRING | No | No | No |  |  | href target tag |
| MiddleClick | BOOLEAN | Yes | No | No | true |  | Enable middle click support on component's click event. |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| Style | STRING | No | No | No |  | TControl | Style information |
| URL | STRING | No | No | No |  |  | External URL of the resource |
| Value | STRING | No | No | Yes |  | TControl | Text value of the control |
| Visible | BOOLEAN | Yes | No | Yes | true | TControl | When true, control is visible, hidden otherwise |

### TLink events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterCreate | GUI | TControl |  |
| onBeforeCreate | GUI | TControl |  |
| onBlur | GUI | TControl | Fired when a user leaves an input field |
| onChange | GUI | TControl | Value change event |
| onClick | GUI | TControl | onClick event |
| onConfirmFalse | GUI | TControl |  |
| onConfirmTrue | GUI | TControl |  |
| onHide | GUI | TControl | Fired when component transitions from shown to hidden |
| onMouseDown | GUI | TControl |  |
| onMouseOut | GUI | TControl |  |
| onMouseOver | GUI | TControl |  |
| onMouseUp | GUI | TControl |  |
| onShow | GUI | TControl | Fired when component transitions from hidden to shown |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TLink methods

| Name | Ancestor | Description |
| --- | --- | --- |
| navigate() |  | Navigate to this action |
| parseControlParameters() | TControl | Parses and executes a number of control parameters |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
| setEnabled() | TControl | Set component enabled/disabled |
| setLabel() | TControl | Sets the label text of a component |
| setReadOnly() | TControl |  |
| setRequired() | TControl |  |
| setValue() | TControl |  |
| setVisible() | TControl |  |

## TMap

*Inheritance: TComponent / TControl / TMap*

**Parent must be: TContainer**

TMap

### TMap properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Attributes | TEXT | No | No | Yes |  | TControl | HTML Special attributes |
| AutoFitZoom | BOOLEAN | Yes | No | Yes | false |  | Run fitZoom() after refresh |
| CenterLatitude | STRING | Yes | No | No |  |  |  |
| CenterLongitude | STRING | Yes | No | No |  |  |  |
| Class | STRING | No | No | No |  | TControl | HTML class. Used for injecting extra classes into the control. |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| Enabled | BOOLEAN | Yes | No | Yes | true | TControl | When true (default), control is enabled. Otherwise control is disabled. |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| HelpText | TEXT | No | No | No |  | TControl | Help text to be displayed for the control |
| MapHeight | NUMBER | Yes | No | No |  |  | Height of map |
| MapTypeControl | BOOLEAN | Yes | No | No | false |  | Map type control enabled or disabled. It allows the user to switch between map types (map, satellite, terrain, etc.). Defaults to false. |
| MapWidth | STRING | Yes | No | No |  |  | Width of the map |
| MarkerClusterer | BOOLEAN | Yes | No | No | false |  |  |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| PanControl | BOOLEAN | Yes | No | No | true |  | Map pan control enabled or disabled. Defaults to false. |
| ReadOnly | BOOLEAN | Yes | No | Yes | false | TControl | List of possible valuesfalse true |
| RefreshOnLoad | BOOLEAN | Yes | No | No | true |  | Run refresh() after load |
| ShowFitZoom | BOOLEAN | Yes | No | No | true |  | List of possible valuesShow or hide fit zoom button on Google Maps |
| ShowLegend | BOOLEAN | Yes | No | Yes | true |  | Shows or hides legend that shows all available map sources. |
| ShowRefresh | BOOLEAN | Yes | No | No | true |  | Show Refresh button on component. Defaults to false. |
| StreetViewControl | BOOLEAN | Yes | No | No | false |  | Map street view feature enabled or disabled. Defaults to false. |
| Style | STRING | No | No | No |  | TControl | Style information |
| Value | STRING | No | No | Yes |  | TControl | Text value of the control |
| Visible | BOOLEAN | Yes | No | Yes | true | TControl | When true, control is visible, hidden otherwise |
| ZoomControl | BOOLEAN | Yes | No | No | true |  | Map zoom controls are displayed or hidden. Defaults to true. |
| ZoomControlPosition | STRING | Yes | No | No | TOP_RIGHT |  | Defines the position of the map zoom control. Defaults to top left. List of possible valuesTOP_CENTER TOP_LEFT TOP_RIGHT LEFT_TOP RIGHT_TOP LEFT_CENTER RIGHT_CENTER LEFT_BOTTOM RIGHT_BOTTOM BOTTOM_CENTER BOTTOM_LEFT BOTTOM_RIGHT |
| ZoomLevel | NUMBER | Yes | No | No | 5 |  | Controls the starting zoom level of the map as follows: 1: World 5: Landmass/continent 10: City 15: Streets 20: Buildings Defaults to 5 |

### TMap events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterCreate | GUI | TControl |  |
| onBeforeCreate | GUI | TControl |  |
| onBlur | GUI | TControl | Fired when a user leaves an input field |
| onChange | GUI | TControl | Value change event |
| onClick | GUI | TControl | onClick event |
| onConfirmFalse | GUI | TControl |  |
| onConfirmTrue | GUI | TControl |  |
| onHide | GUI | TControl | Fired when component transitions from shown to hidden |
| onMarkerClick | GUI |  | Fires when a marker is clicked |
| onMouseDown | GUI | TControl |  |
| onMouseOut | GUI | TControl |  |
| onMouseOver | GUI | TControl |  |
| onMouseUp | GUI | TControl |  |
| onShow | GUI | TControl | Fired when component transitions from hidden to shown |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TMap methods

| Name | Ancestor | Description |
| --- | --- | --- |
| fitZoom() |  | Map will zoom to fit all visual components |
| parseControlParameters() | TControl | Parses and executes a number of control parameters |
| refresh() |  | Refreshes component data |
| setCenter() |  |  |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
| setEnabled() | TControl | Set component enabled/disabled |
| setLabel() | TControl | Sets the label text of a component |
| setReadOnly() | TControl |  |
| setRequired() | TControl |  |
| setValue() | TControl |  |
| setVisible() | TControl |  |

## TDataProvider

*Inheritance: TComponent / TDataProvider*

**Parent must be: only inherited types can be placed**

TDataProvider

### TDataProvider properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| AutoOpenAllowed | BOOLEAN | No | Yes | No | true |  | AutoOpen component between construct and render time. Defaults to false. |
| CacheID | STRING | Yes | No | No | @this.Name |  | Cache ID |
| CacheInfo | ARRAY | No | Yes | No |  |  | Cache info |
| CacheMode | STRING | Yes | No | No | ReadWrite |  | Cache access mode. In ReadWrite mode the component writes its result to the cache and reads it the next time it is opened. In WriteOnly mode the component always writes the cache, never reads it. In ReadOnly mode, the component always reads the cache, never writes. List of possible valuesReadWrite WriteOnly ReadOnly |
| CachePartitionedBy | STRING | No | No | No |  |  | The column name partitioning by. If empty the whole result will be cached, otherwise th result array will be cached in an indexed array with the first column. |
| CachePartitionFilter | TQueryFilter | No | No | No |  |  | Cache partition parameter. If exists and cache is partitioned only the referenced index will be refreshed. |
| CacheRefresh | BOOLEAN | No | Yes | No |  |  | Force cache to refresh |
| CacheSQLConflict | STRING | No | No | No |  |  | What to do if the SQL stored in cache is different from the actual one List of possible valuesReadCache DisableCaching RewriteCache |
| CacheUsed | BOOLEAN | No | Yes | No |  |  | Cache is used for reading data |
| CacheValidity | NUMBER | No | No | No |  |  | Cache validity in minutes |
| Caching | STRING | Yes | No | No | Disabled |  | Caching methodology List of possible valuesDisabled Private Shared |
| DatabaseIndex | NUMBER | No | No | No |  |  | Database index in the config file to be used for database operations. It allows using multiple databases in an application. Defaults to 1. |
| DataProxy | TDataProxy | No | No | No |  |  | DataProxy component |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| PlainTextFormat | STRING | No | No | No | VALUE |  | Plain text format List of possible valuesKEY=VALUE KEY:VALUE KEY VALUE |
| PlainTextRecordSeparator | STRING | No | No | No |  |  |  |
| ResponseType | STRING | Yes | No | No | JSON |  | Type of the generated response. HTML will render a full HTML5-compliant page while HTMLSnippet will only render a partial useful for AJAX page generators. JSON and PLAINTEXT are self-explanatory. List of possible valuesHTML JSON JSONDATA PLAINTEXT XML NONE |
| XMLNamespace | STRING | No | No | No |  |  |  |
| XMLRootElement | STRING | No | No | No |  |  |  |
| XMLRowElement | STRING | No | No | No |  |  |  |

### TDataProvider events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeOpen | PHP |  |  |
| onBeforeRender | PHP | TComponent |  |
| onError | PHP |  |  |
| onSuccess | PHP |  |  |

### TDataProvider methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TExternalDataProvider

*Inheritance: TComponent / TDataProvider / TExternalDataProvider*

**Parent must be: TRoute**

TExternalDataProvider

### TExternalDataProvider properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| AutoOpenAllowed | BOOLEAN | Yes | No | No | false | TDataProvider | AutoOpen component between construct and render time. Defaults to false. |
| DataProxy | TDataProxy | No | No | No |  | TDataProvider | DataProxy component |
| DataResultField | STRING | Yes | No | No | result |  |  |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| HTTPRequestHeader | TEXT | Yes | No | No | @(parameter).Tholos.HTTPRequestHeader |  |  |
| HTTPRequestMethod | STRING | Yes | No | No | POST |  | List of possible valuesGET HEAD POST PUT DELETE CONNECT OPTIONS TRACE PATCH |
| JSONParameters | ARRAY | No | Yes | No |  |  |  |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | No |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| PlainTextFormat | STRING | No | No | No | KEY=VALUE | TDataProvider | Plain text format List of possible valuesKEY=VALUE KEY:VALUE KEY VALUE |
| PlainTextRecordSeparator | STRING | No | No | No | , | TDataProvider |  |
| QueryLimit | NUMBER | No | Yes | No | 0 |  |  |
| QueryOffset | NUMBER | No | Yes | No | 0 |  |  |
| ResponseType | STRING | Yes | No | No | JSON | TDataProvider | Type of the generated response. HTML will render a full HTML5-compliant page while HTMLSnippet will only render a partial useful for AJAX page generators. JSON and PLAINTEXT are self-explanatory. List of possible valuesHTML JSON JSONDATA PLAINTEXT XML NONE |
| Result | TEXT | No | Yes | No |  |  |  |
| ResultType | STRING | No | Yes | No | ARRAY |  | List of possible valuesARRAY JSON |
| RowCount | NUMBER | No | Yes | No |  |  |  |
| TimeOut | NUMBER | No | No | No | 0 |  |  |
| TotalRowCount | NUMBER | No | Yes | No |  |  |  |
| TotalRowCountField | STRING | No | No | No | totalRowCount |  |  |
| URL | STRING | Yes | No | No |  |  | External URL of the resource |
| URLPath | STRING | No | No | No |  |  |  |
| XMLNamespace | STRING | No | No | No |  | TDataProvider |  |
| XMLRootElement | STRING | No | No | No |  | TDataProvider |  |
| XMLRowElement | STRING | No | No | No |  | TDataProvider |  |

### TExternalDataProvider events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeOpen | PHP | TDataProvider |  |
| onBeforeRender | PHP | TComponent |  |
| onError | PHP | TDataProvider |  |
| onSuccess | PHP | TDataProvider |  |

### TExternalDataProvider methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TFileProcessor

*Inheritance: TComponent / TDataProvider / TFileProcessor*

**Parent must be: TAction**

TFileProcessor

### TFileProcessor properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| AutoOpenAllowed | BOOLEAN | No | Yes | No | true | TDataProvider | AutoOpen component between construct and render time. Defaults to false. |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| ExtractArchive | BOOLEAN | Yes | No | No | false |  | Extract ZIP archive in case it uploaded |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| LocalFilePath | PARAMETER | Yes | No | No |  |  |  |
| MaximumFilesInArchive | NUMBER | Yes | No | No | 0 |  | The number of files archive can contain |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| Opened | BOOLEAN | No | Yes | No | false |  |  |
| PlainTextFormat | STRING | No | No | No | VALUE | TDataProvider | Plain text format List of possible valuesKEY=VALUE KEY:VALUE KEY VALUE |
| PlainTextRecordSeparator | STRING | No | No | No |  | TDataProvider |  |
| ResponseType | STRING | Yes | No | No | JSON | TDataProvider | Type of the generated response. HTML will render a full HTML5-compliant page while HTMLSnippet will only render a partial useful for AJAX page generators. JSON and PLAINTEXT are self-explanatory. List of possible valuesHTML JSON JSONDATA PLAINTEXT XML NONE |
| XMLNamespace | STRING | No | No | No |  | TDataProvider |  |
| XMLRootElement | STRING | No | No | No |  | TDataProvider |  |
| XMLRowElement | STRING | No | No | No |  | TDataProvider |  |

### TFileProcessor events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeOpen | PHP | TDataProvider |  |
| onBeforeRender | PHP | TComponent |  |
| onError | PHP | TDataProvider |  |
| onSuccess | PHP | TDataProvider |  |

### TFileProcessor methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TJSONDataProvider

*Inheritance: TComponent / TDataProvider / TJSONDataProvider*

**Parent must be: TDataProvider**

TJSONDataProvider

### TJSONDataProvider properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| AutoOpenAllowed | BOOLEAN | No | Yes | Yes | false | TDataProvider | AutoOpen component between construct and render time. Defaults to false. |
| DataResultField | STRING | Yes | No | Yes |  |  |  |
| DevNote | TEXT | No | No | Yes |  | TComponent | Developer Note |
| FunctionCode | STRING | No | No | Yes |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| ResponseType | STRING | No | No | Yes |  | TDataProvider | Type of the generated response. HTML will render a full HTML5-compliant page while HTMLSnippet will only render a partial useful for AJAX page generators. JSON and PLAINTEXT are self-explanatory. List of possible valuesHTML JSON JSONDATA PLAINTEXT XML NONE |

### TJSONDataProvider events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeOpen | PHP | TDataProvider |  |
| onBeforeRender | PHP | TComponent |  |
| onError | PHP | TDataProvider |  |
| onSuccess | PHP | TDataProvider |  |

### TJSONDataProvider methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TQuery

*Inheritance: TComponent / TDataProvider / TQuery*

**Parent must be: TRoute**

TQuery

### TQuery properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| AuthProcedure | TStoredProcedure | No | No | No |  |  | Authentication procedure |
| AutoOpenAllowed | BOOLEAN | No | Yes | No | true | TDataProvider | AutoOpen component between construct and render time. Defaults to false. |
| CacheID | STRING | Yes | No | No | @this.Name | TDataProvider | Cache ID |
| CacheInfo | ARRAY | No | Yes | No |  | TDataProvider | Cache info |
| CacheMode | STRING | Yes | No | No | ReadWrite | TDataProvider | Cache access mode. In ReadWrite mode the component writes its result to the cache and reads it the next time it is opened. In WriteOnly mode the component always writes the cache, never reads it. In ReadOnly mode, the component always reads the cache, never writes. List of possible valuesReadWrite WriteOnly ReadOnly |
| CachePartitionedBy | STRING | No | No | No |  | TDataProvider | The column name partitioning by. If empty the whole result will be cached, otherwise th result array will be cached in an indexed array with the first column. |
| CachePartitionFilter | TQueryFilter | No | No | No |  | TDataProvider | Cache partition parameter. If exists and cache is partitioned only the referenced index will be refreshed. |
| CacheRefresh | BOOLEAN | No | Yes | No |  | TDataProvider | Force cache to refresh |
| CacheSQLConflict | STRING | Yes | No | No | DisableCaching | TDataProvider | What to do if the SQL stored in cache is different from the actual one List of possible valuesReadCache DisableCaching RewriteCache |
| CacheUsed | BOOLEAN | No | Yes | No |  | TDataProvider | Cache is used for reading data |
| CacheValidity | NUMBER | No | No | No |  | TDataProvider | Cache validity in minutes |
| Caching | STRING | Yes | No | No | Disabled | TDataProvider | Caching methodology List of possible valuesDisabled Private Shared |
| CountTotalRows | BOOLEAN | No | Yes | No | false |  | If true TQuery will count the total number of result before generating result. TotalRowCount property will hold the value. It may differ from RowCount property which is the number of rows given back after QueryLimit applied. |
| DatabaseIndex | NUMBER | No | No | No |  | TDataProvider | Database index in the config file to be used for database operations. It allows using multiple databases in an application. Defaults to 1. |
| DataProxy | TDataProxy | No | No | No |  | TDataProvider | DataProxy component |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| DisableQueryFilters | BOOLEAN | No | Yes | No | false |  |  |
| DynamicMode | BOOLEAN | Yes | No | No | false |  | In dynamic mode TQuery object runs query two times: first query has to give back a new SQL command, which will be queried on the second run. |
| Filter | TEXT | No | No | No |  |  | Additional where clause |
| FilterArray | ARRAY | No | Yes | No |  |  |  |
| FilterError | BOOLEAN | No | Yes | Yes | false |  | Required filter missing, stop execution. |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| GlobalParameterPrefix | STRING | No | No | Yes |  |  | Adds all fields to the global parameter list with prefix |
| InitProcedure | TStoredProcedure | No | No | No |  |  | Initialization procedure - runs before query open |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | No |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| Opened | BOOLEAN | No | Yes | No | false |  |  |
| OrderBy | STRING | No | No | No |  |  |  |
| PlainTextFormat | STRING | No | No | No | KEY=VALUE | TDataProvider | Plain text format List of possible valuesKEY=VALUE KEY:VALUE KEY VALUE |
| PlainTextHeader | BOOLEAN | Yes | No | No | false |  | Add columns as header to plain text output |
| PlainTextRecordSeparator | STRING | No | No | No | , | TDataProvider |  |
| PreparedSQL | TEXT | No | Yes | No |  |  |  |
| QueryLimit | NUMBER | No | Yes | No | 0 |  |  |
| QueryOffset | NUMBER | No | Yes | No | 0 |  |  |
| ResponseType | STRING | Yes | No | No | JSON | TDataProvider | Type of the generated response. HTML will render a full HTML5-compliant page while HTMLSnippet will only render a partial useful for AJAX page generators. JSON and PLAINTEXT are self-explanatory. List of possible valuesHTML JSON JSONDATA PLAINTEXT XML NONE |
| Result | TEXT | No | Yes | No |  |  |  |
| ResultType | STRING | No | Yes | No | ARRAY |  | List of possible valuesARRAY JSON |
| RowCount | NUMBER | No | Yes | No |  |  |  |
| SQL | TEXT | Yes | No | No |  |  | SQL statement |
| StructureInfoOnly | BOOLEAN | No | Yes | No | false |  | It gives back (and caches) the query structure only *where 0=1". |
| TotalRowCount | NUMBER | No | Yes | No |  |  |  |
| TotalRowCountField | STRING | No | No | No |  |  |  |
| TotalRowCountSQL | STRING | No | No | No | @(parameter).TotalRowCountSQL |  |  |
| XMLNamespace | STRING | No | No | No |  | TDataProvider |  |
| XMLRootElement | STRING | No | No | No | rows | TDataProvider |  |
| XMLRowElement | STRING | No | No | No | row | TDataProvider |  |

### TQuery events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onAuthError | PHP |  |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeOpen | PHP | TDataProvider |  |
| onBeforeRender | PHP | TComponent |  |
| onError | PHP | TDataProvider |  |
| onInitError | PHP |  |  |
| onSuccess | PHP | TDataProvider |  |

### TQuery methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TQueryGroup

*Inheritance: TComponent / TDataProvider / TQueryGroup*

**Parent must be: TRoute**

TQueryGroup

### TQueryGroup properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| AutoOpenAllowed | BOOLEAN | No | Yes | Yes | true | TDataProvider | AutoOpen component between construct and render time. Defaults to false. |
| CacheID | STRING | Yes | No | No | @this.Name | TDataProvider | Cache ID |
| CacheInfo | ARRAY | No | Yes | No |  | TDataProvider | Cache info |
| CacheMode | STRING | Yes | No | No | ReadWrite | TDataProvider | Cache access mode. In ReadWrite mode the component writes its result to the cache and reads it the next time it is opened. In WriteOnly mode the component always writes the cache, never reads it. In ReadOnly mode, the component always reads the cache, never writes. List of possible valuesReadWrite WriteOnly ReadOnly |
| CacheRefresh | BOOLEAN | No | Yes | No |  | TDataProvider | Force cache to refresh |
| CacheSQLConflict | STRING | Yes | No | Yes | DisableCaching | TDataProvider | What to do if the SQL stored in cache is different from the actual one List of possible valuesReadCache DisableCaching RewriteCache |
| CacheUsed | BOOLEAN | No | Yes | No |  | TDataProvider | Cache is used for reading data |
| CacheValidity | NUMBER | No | No | No |  | TDataProvider | Cache validity in minutes |
| Caching | STRING | Yes | No | No | Disabled | TDataProvider | Caching methodology List of possible valuesDisabled Private Shared |
| DataProxy | TDataProxy | No | No | No |  | TDataProvider | DataProxy component |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FunctionCode | STRING | No | No | Yes |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| ResponseType | STRING | Yes | No | Yes | JSON | TDataProvider | Type of the generated response. HTML will render a full HTML5-compliant page while HTMLSnippet will only render a partial useful for AJAX page generators. JSON and PLAINTEXT are self-explanatory. List of possible valuesHTML JSON JSONDATA PLAINTEXT XML NONE |

### TQueryGroup events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeOpen | PHP | TDataProvider |  |
| onBeforeRender | PHP | TComponent |  |
| onError | PHP | TDataProvider |  |
| onSuccess | PHP | TDataProvider |  |

### TQueryGroup methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TStoredProcedure

*Inheritance: TComponent / TDataProvider / TStoredProcedure*

**Parent must be: TRoute**

TStoredProcedure

### TStoredProcedure properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| AutoOpenAllowed | BOOLEAN | No | Yes | No | false | TDataProvider | AutoOpen component between construct and render time. Defaults to false. |
| CallbackParameter | TDBParam | No | No | No |  |  | Parameter contains the Tholos control structure, which will passed back to the GUI |
| CallbackResult | JSON | No | Yes | No |  |  | Runtime parameter, contains the Tholos control structure which will passed back to the GUI |
| DatabaseIndex | NUMBER | No | No | No |  | TDataProvider | Database index in the config file to be used for database operations. It allows using multiple databases in an application. Defaults to 1. |
| DataProxy | TDataProxy | No | No | No |  | TDataProvider | DataProxy component |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| ErrorCodeOnSuccess | NUMBER | No | No | No | 0 |  |  |
| ErrorCodeParameter | TDBParam | No | No | No |  |  |  |
| ErrorMessageParameter | TDBParam | No | No | No |  |  |  |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| GenerateDataResult | BOOLEAN | Yes | No | No | true |  | Give parameters back to the caller |
| LogDBParameterName | STRING | No | No | No | @this.LogParameter.Name |  |  |
| LoggerStoredProcedure | TStoredProcedure | No | No | No |  |  | Write back the received Log (PostgreSQL - Autonomous Transaction fix) |
| LogParameter | TDBParam | No | No | No |  |  | Log parameter (OUT) |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | No |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| ParameterNameTransformation | STRING | Yes | No | Yes | NONE |  | List of possible valuesNONE UPPERCASE LOWERCASE CAMELCASE UNCAMELCASE |
| ParameterPrefix | STRING | No | No | No | P_ |  | Ha meg van adva, akkor ezzel a prefix-szel ellátott névvel keresi a TDBParam-okhoz az értékeket. Tehát, TDBParam=p_ID és a prefix p_, akkor az ID input a p_ID paraméterbe fog kerülni. |
| PlainTextFormat | STRING | No | No | No | VALUE | TDataProvider | Plain text format List of possible valuesKEY=VALUE KEY:VALUE KEY VALUE |
| Procedure | STRING | Yes | No | No |  |  | Name of the SQL function |
| ResponseType | STRING | Yes | No | No | JSON | TDataProvider | Type of the generated response. HTML will render a full HTML5-compliant page while HTMLSnippet will only render a partial useful for AJAX page generators. JSON and PLAINTEXT are self-explanatory. List of possible valuesHTML JSON JSONDATA PLAINTEXT XML NONE |
| Result | TEXT | No | Yes | No |  |  |  |
| ResultErrorCode | NUMBER | No | Yes | No |  |  |  |
| ResultErrorMessage | STRING | No | Yes | No |  |  |  |
| ResultType | STRING | No | Yes | No | JSON |  | List of possible valuesARRAY JSON |
| RollbackOnError | BOOLEAN | Yes | No | No | true |  | Rollback in case if Errorcode is not equal to ErrorCodeOnSuccess |
| Success | BOOLEAN | No | Yes | No | false |  | Execution success |
| ThrowException | BOOLEAN | Yes | No | No | false |  |  |
| TransactionMode | BOOLEAN | Yes | No | No | true |  | Use transaction |
| WriteErrorLogOnError | BOOLEAN | Yes | No | No | false |  | WriteErrorLog in case of handled error |
| XMLNamespace | STRING | No | No | No |  | TDataProvider |  |
| XMLRootElement | STRING | No | No | No |  | TDataProvider |  |
| XMLRowElement | STRING | No | No | No |  | TDataProvider |  |

### TStoredProcedure events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeOpen | PHP | TDataProvider |  |
| onBeforeRender | PHP | TComponent |  |
| onError | PHP | TDataProvider |  |
| onSuccess | PHP | TDataProvider |  |

### TStoredProcedure methods

| Name | Ancestor | Description |
| --- | --- | --- |
| open() |  |  |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TAPIPost

*Inheritance: TComponent / TDataProvider / TStoredProcedure / TAPIPost*

**Parent must be: TRoute**

TAPIPost

### TAPIPost properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| AutoOpenAllowed | BOOLEAN | No | Yes | No | false | TDataProvider | AutoOpen component between construct and render time. Defaults to false. |
| CallbackParameter | TDBParam | No | No | No |  | TStoredProcedure | Parameter contains the Tholos control structure, which will passed back to the GUI |
| CallbackResult | JSON | No | No | No |  | TStoredProcedure | Runtime parameter, contains the Tholos control structure which will passed back to the GUI |
| DataProxy | TDataProxy | No | No | No |  | TDataProvider | DataProxy component |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| ErrorCodeJSONField | STRING | No | No | No | errorCode |  |  |
| ErrorCodeOnSuccess | NUMBER | No | No | No |  | TStoredProcedure |  |
| ErrorCodeParameter | TDBParam | No | No | No |  | TStoredProcedure |  |
| ErrorMessageJSONField | STRING | No | No | No | errorMsg |  |  |
| ErrorMessageParameter | TDBParam | No | No | No |  | TStoredProcedure |  |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| GenerateDataResult | BOOLEAN | Yes | No | No | true | TStoredProcedure | Give parameters back to the caller |
| HTTPRequestHeader | TEXT | Yes | No | No | @(parameter).Tholos.HTTPRequestHeader |  |  |
| HTTPRequestMethod | STRING | Yes | No | No | POST |  | List of possible valuesGET HEAD POST PUT DELETE CONNECT OPTIONS TRACE PATCH |
| HTTPResponseType | STRING | Yes | No | No | JSON |  | JSON HTML XML |
| LogDBParameterName | STRING | No | No | No |  | TStoredProcedure |  |
| LoggerStoredProcedure | TStoredProcedure | No | No | No |  | TStoredProcedure | Write back the received Log (PostgreSQL - Autonomous Transaction fix) |
| LogParameter | TDBParam | No | No | No |  | TStoredProcedure | Log parameter (OUT) |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | No |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| ParameterNameTransformation | STRING | Yes | No | No | NONE | TStoredProcedure | List of possible valuesNONE UPPERCASE LOWERCASE CAMELCASE UNCAMELCASE |
| ParameterPrefix | STRING | No | No | No |  | TStoredProcedure | Ha meg van adva, akkor ezzel a prefix-szel ellátott névvel keresi a TDBParam-okhoz az értékeket. Tehát, TDBParam=p_ID és a prefix p_, akkor az ID input a p_ID paraméterbe fog kerülni. |
| PlainTextFormat | STRING | No | No | No |  | TDataProvider | Plain text format List of possible valuesKEY=VALUE KEY:VALUE KEY VALUE |
| ResponseType | STRING | Yes | No | No | JSON | TDataProvider | Type of the generated response. HTML will render a full HTML5-compliant page while HTMLSnippet will only render a partial useful for AJAX page generators. JSON and PLAINTEXT are self-explanatory. List of possible valuesHTML JSON JSONDATA PLAINTEXT XML NONE |
| Result | TEXT | No | Yes | No |  | TStoredProcedure |  |
| ResultErrorCode | NUMBER | No | Yes | No |  | TStoredProcedure |  |
| ResultErrorMessage | STRING | No | Yes | No |  | TStoredProcedure |  |
| ResultType | STRING | No | Yes | No |  | TStoredProcedure | List of possible valuesARRAY JSON |
| SendAuthHeader | BOOLEAN | Yes | No | No | true |  | Send auth header according to TRoleManager.LoginIDHeader |
| Success | BOOLEAN | No | Yes | No |  | TStoredProcedure | Execution success |
| ThrowException | BOOLEAN | Yes | No | No | false | TStoredProcedure |  |
| TimeOut | NUMBER | No | No | Yes | 0 |  |  |
| URL | STRING | No | No | No | @(parameter).DevAPIURL\|@(parameter).DefaultAPIURL |  | External URL of the resource |
| URLPath | STRING | No | No | No |  |  |  |
| WriteErrorLogOnError | BOOLEAN | Yes | No | No | false | TStoredProcedure | WriteErrorLog in case of handled error |
| XMLNamespace | STRING | No | No | No |  | TDataProvider |  |
| XMLRootElement | STRING | No | No | No |  | TDataProvider |  |
| XMLRowElement | STRING | No | No | No |  | TDataProvider |  |

### TAPIPost events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeOpen | PHP | TDataProvider |  |
| onBeforeRender | PHP | TComponent |  |
| onError | PHP | TDataProvider |  |
| onSuccess | PHP | TDataProvider |  |

### TAPIPost methods

| Name | Ancestor | Description |
| --- | --- | --- |
| open() | TStoredProcedure |  |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TDataProxy

*Inheritance: TComponent / TDataProxy*

**Parent must be: TApplication**

TDataProxy

### TDataProxy properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| AJAXMode | BOOLEAN | Yes | No | No | true |  | Fetches associated list at render time (false) or loading time via AJAX (true) |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| Enabled | BOOLEAN | Yes | No | No | true |  | When true (default), control is enabled. Otherwise control is disabled. |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | No |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| URL | STRING | Yes | No | No |  |  | External URL of the resource |

### TDataProxy events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TDataProxy methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TDBField

*Inheritance: TComponent / TDBField*

**Parent must be: TDataProvider**

TDBField

### TDBField properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| DataType | STRING | Yes | No | Yes |  |  | Data type of the field List of possible valuesinteger string text bool date datetime datetimehm timestamp time float JSON |
| DateFormatParameter | STRING | No | No | Yes | @this.datatype |  | DateFormatParameter will be prefixed with PHP\|JS\|DB to convert date\|datetime\|time data types from DBValue to Value and back. Date format is multilingual compatible. Lang parameter will be concatenated. List of possible valuesdate datetime datetimehm dateUniversal |
| DBValue | STRING | No | Yes | Yes |  |  |  |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FieldName | STRING | Yes | No | Yes |  |  | Field name in the database table |
| FormatString | STRING | No | No | Yes |  |  | Formatting value property |
| FunctionCode | STRING | No | No | Yes |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Index | NUMBER | Yes | No | Yes |  |  | Index of field in query |
| Label | STRING | Yes | No | Yes | @this.FieldName |  | Label text of the control |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| NativeDataType | STRING | Yes | No | Yes |  |  | List of possible valuesOCI8.BFILE OCI8.BLOB OCI8.CFILE OCI8.CHAR OCI8.CLOB OCI8.DATE OCI8.DATETIME OCI8.MLSLABEL OCI8.NAMEDCOLLECTION OCI8.NCHAR OCI8.NCLOB OCI8.NUMBER OCI8.OBJECT OCI8.OPAQUE OCI8.RAW OCI8.REF OCI8.TABLE OCI8.TIMESTAMP OCI8.TIMESTAMP_DS OCI8.TIMESTAMP_LTZ OCI8.TIMESTAMP_TZ OCI8.TIMESTAMP_YM OCI8.VARCHAR OCI8.VARCHAR2 OCI8.VARRAY PGSQL.ANYARRAY PGSQL.BOOL PGSQL.BYTEA PGSQL.CHAR PGSQL.DATE PGSQL.FLOAT4 PGSQL.FLOAT8 PGSQL.INET PGSQL.INT2 PGSQL.INT2VECTOR PGSQL.INT4 PGSQL.INT8 PGSQL.INTERVAL PGSQL.NAME PGSQL.NUMERIC PGSQL.OID PGSQL.OIDVECTOR PGSQL.PG_DEPENDENCIES PGSQL.PG_LSN PGSQL.PG_MCV_LIST PGSQL.PG_NDISTINCT PGSQL.PG_NODE_TREE PGSQL.REGPROC PGSQL.REGTYPE PGSQL.TEXT PGSQL.TIMESTAMP PGSQL.TIMESTAMPTZ PGSQL.VARCHAR PGSQL.XID JSON.STRING JSON.INTEGER JSON.NUMBER JSON.BOOL JSON.TIMESTAMP JSON.DATE JSON.TIME JSON.DATE-TIME |
| NullResultParameter | PARAMETER | No | No | Yes |  |  |  |
| ParseValue | BOOLEAN | Yes | No | Yes | false |  | Parse value as Tholos command sequence |
| Size | NUMBER | No | No | Yes |  |  |  |
| Value | STRING | No | Yes | Yes |  |  | Text value of the control |
| ValueChanged | BOOLEAN | No | Yes | Yes |  |  | Value changed |

### TDBField events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |
| onSetValue | PHP |  |  |

### TDBField methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TJSONField

*Inheritance: TComponent / TDBField / TJSONField*

**Parent must be: TDBField**

TJSONField

### TJSONField properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| DataType | STRING | Yes | No | Yes | string | TDBField | Data type of the field List of possible valuesinteger string text bool date datetime datetimehm timestamp time float JSON |
| DateFormatParameter | STRING | No | No | Yes | @this.datatype | TDBField | DateFormatParameter will be prefixed with PHP\|JS\|DB to convert date\|datetime\|time data types from DBValue to Value and back. Date format is multilingual compatible. Lang parameter will be concatenated. List of possible valuesdate datetime datetimehm dateUniversal |
| DBValue | STRING | No | Yes | Yes |  | TDBField |  |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FieldName | STRING | Yes | No | Yes |  | TDBField | Field name in the database table |
| FormatString | STRING | No | No | Yes |  | TDBField | Formatting value property |
| FunctionCode | STRING | No | No | Yes |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Label | STRING | Yes | No | Yes | @this.FieldName | TDBField | Label text of the control |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| NullResultParameter | PARAMETER | No | No | Yes |  | TDBField |  |
| ParseValue | BOOLEAN | Yes | No | Yes | false | TDBField | Parse value as Tholos command sequence |
| Value | STRING | No | Yes | Yes |  | TDBField | Text value of the control |
| ValueChanged | BOOLEAN | No | No | Yes |  | TDBField | Value changed |

### TJSONField events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |
| onSetValue | PHP | TDBField |  |

### TJSONField methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TDBParam

*Inheritance: TComponent / TDBParam*

**Parent must be: TDataProvider**

TDBParam

### TDBParam properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| AddToResult | BOOLEAN | Yes | No | Yes | true |  | Parameter will be given back to the caller |
| BindParameterName | STRING | Yes | No | Yes | @this.ParameterName |  |  |
| DataType | STRING | Yes | No | Yes |  |  | Data type of the field List of possible valuesinteger string text bool date datetime datetimehm timestamp time float JSON |
| DateFormatParameter | STRING | No | No | Yes | @this.DataType |  | DateFormatParameter will be prefixed with PHP\|JS\|DB to convert date\|datetime\|time data types from DBValue to Value and back. Date format is multilingual compatible. Lang parameter will be concatenated. List of possible valuesdate datetime datetimehm dateUniversal |
| DefaultValue | STRING | No | No | Yes |  |  |  |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FunctionCode | STRING | No | No | Yes |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| MDB2DataType | STRING | No | No | Yes |  |  | List of possible valuestext boolean integer decimal float date time timestamp clob blob |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| NativeDataType | STRING | Yes | No | Yes |  |  | List of possible valuesOCI8.BFILE OCI8.BLOB OCI8.CFILE OCI8.CHAR OCI8.CLOB OCI8.DATE OCI8.DATETIME OCI8.MLSLABEL OCI8.NAMEDCOLLECTION OCI8.NCHAR OCI8.NCLOB OCI8.NUMBER OCI8.OBJECT OCI8.OPAQUE OCI8.RAW OCI8.REF OCI8.TABLE OCI8.TIMESTAMP OCI8.TIMESTAMP_DS OCI8.TIMESTAMP_LTZ OCI8.TIMESTAMP_TZ OCI8.TIMESTAMP_YM OCI8.VARCHAR OCI8.VARCHAR2 OCI8.VARRAY PGSQL.ANYARRAY PGSQL.BOOL PGSQL.BYTEA PGSQL.CHAR PGSQL.DATE PGSQL.FLOAT4 PGSQL.FLOAT8 PGSQL.INET PGSQL.INT2 PGSQL.INT2VECTOR PGSQL.INT4 PGSQL.INT8 PGSQL.INTERVAL PGSQL.NAME PGSQL.NUMERIC PGSQL.OID PGSQL.OIDVECTOR PGSQL.PG_DEPENDENCIES PGSQL.PG_LSN PGSQL.PG_MCV_LIST PGSQL.PG_NDISTINCT PGSQL.PG_NODE_TREE PGSQL.REGPROC PGSQL.REGTYPE PGSQL.TEXT PGSQL.TIMESTAMP PGSQL.TIMESTAMPTZ PGSQL.VARCHAR PGSQL.XID JSON.STRING JSON.INTEGER JSON.NUMBER JSON.BOOL JSON.TIMESTAMP JSON.DATE JSON.TIME JSON.DATE-TIME |
| ParameterMapping | JSON | No | No | Yes |  |  | In case of JSON type parameter, this property contains the parameter mapping options |
| ParameterMode | STRING | Yes | No | Yes | IN |  | List of possible valuesIN OUT INOUT |
| ParameterName | STRING | Yes | No | Yes | @this.Name |  |  |
| ParseValue | BOOLEAN | Yes | No | Yes | false |  | Parse value as Tholos command sequence |
| SuppressDataTypeError | BOOLEAN | Yes | No | Yes | false |  | In case of inappropriate formatted input data the property controls whether an exception must be dropped or not. Use it carefully, it might hides programing errors! |
| Value | STRING | No | Yes | Yes |  |  | Text value of the control |

### TDBParam events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TDBParam methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TDiagramEditor

*Inheritance: TComponent / TDiagramEditor*

**Parent must be: TContainer**

TDiagramEditor

### TDiagramEditor properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| AllowLoopback | BOOLEAN | Yes | No | Yes | false |  | Allow loopback node connection |
| ControlHeight | STRING | Yes | No | Yes | 80vh |  | Height of the control |
| ControlWidth | STRING | Yes | No | Yes | 80vw |  | Width of the control |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FunctionCode | STRING | No | No | Yes |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| ListSourceConnectors | TQuery | Yes | No | Yes |  |  | List of connectors (source_id, target_id, type, [options]) |
| ListSourceNodes | TQuery | Yes | No | Yes |  |  | List of nodes (id, text, [options]) |
| Master | TControl | No | No | Yes |  |  | Used in dependant control situations. Specifies the master component which this control depends on. When master component refreshes its value, this component will also be refreshed. |
| MasterFilterField | STRING | No | No | Yes |  |  |  |
| Name | STRING | No | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |

### TDiagramEditor events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onConnect | GUI |  |  |
| onDisconnect | GUI |  |  |
| onMove | GUI |  |  |
| onSave | GUI |  |  |
| onSelect | GUI |  |  |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TDiagramEditor methods

| Name | Ancestor | Description |
| --- | --- | --- |
| refresh() |  | Refreshes component data |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TDocumentTitle

*Inheritance: TComponent / TDocumentTitle*

**Parent must be: TComponent**

TDocumentTitle

### TDocumentTitle properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| Tag | STRING | Yes | No | No | tholosDocumentTitle |  | HTML tag |
| Value | STRING | Yes | No | No |  |  | Text value of the control |

### TDocumentTitle events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TDocumentTitle methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TDPOpen

*Inheritance: TComponent / TDPOpen*

**Parent must be: TComponent**

TDPOpen

### TDPOpen properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| CloseDataSourceBeforeOpen | BOOLEAN | Yes | No | No | false |  |  |
| DataSource | TDataProvider | Yes | No | No |  |  |  |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |

### TDPOpen events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TDPOpen methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TGridControls

*Inheritance: TComponent / TGridControls*

**Parent must be: only inherited types can be placed**

TGridControls

### TGridControls properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| DevNote | TEXT | No | No | Yes |  | TComponent | Developer Note |
| FunctionCode | STRING | No | No | Yes |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |

### TGridControls events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TGridControls methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TGridColumn

*Inheritance: TComponent / TGridControls / TGridColumn*

**Parent must be: TComponent**

TGridColumn

### TGridColumn properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Align | STRING | Yes | No | No | left |  | Content alignment List of possible valuesleft center right |
| ChartOptions | JSON | No | No | No | {} |  | ChartJS options |
| ChartType | STRING | No | No | No |  |  | Chart available based on grid values List of possible valuesbar line radar polarArea pie doughnut |
| Class | STRING | No | No | No |  |  | HTML class. Used for injecting extra classes into the control. |
| ColumnOffset | NUMBER | Yes | No | No | 0 |  |  |
| ColumnSpan | NUMBER | No | No | No |  |  | ColumnSpan in table |
| DBField | TDBField | Yes | No | No |  |  | Database field attached to the control. When specified, the control is database-aware. |
| DBValue | STRING | No | Yes | No |  |  |  |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| Exportable | BOOLEAN | Yes | No | No | true |  | This control can be exported eg. to Excel |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| GridFilter | TGridFilter | No | No | No |  |  |  |
| HeadClass | STRING | No | No | No |  |  | Class of column's head |
| HeadStyle | STRING | No | No | No |  |  | Style of column's head |
| Label | STRING | Yes | No | No | @this.DBField.Label |  | Label text of the control |
| MarkChanges | BOOLEAN | Yes | No | No | false |  | Mark changes in the grid cell |
| Name | STRING | Yes | No | No |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | No |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| Sortable | BOOLEAN | Yes | No | No | true |  | Column is sortable |
| Sorted | STRING | No | Yes | No |  |  | List of possible valuesASC DESC |
| SortingDirection | STRING | Yes | No | No | ASC |  | List of possible valuesASC DESC |
| Style | STRING | No | No | No |  |  | Style information |
| Template | TEMPLATE | No | No | No |  |  | Template for freestyle formatting :) |
| Value | STRING | No | Yes | No |  |  | Text value of the control |
| ValueChanged | BOOLEAN | No | Yes | No | false |  | Value changed |
| ValueChangedClass | STRING | No | No | No |  |  |  |
| ValueTemplate | TEMPLATE | No | No | No |  |  | Template for value formatting only |
| Visible | BOOLEAN | Yes | No | No | true |  | When true, control is visible, hidden otherwise |

### TGridColumn events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TGridColumn methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TGridFilter

*Inheritance: TComponent / TGridControls / TGridFilter*

**Parent must be: TGrid**

TGridFilter

### TGridFilter properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| CanBeNULL | BOOLEAN | Yes | No | Yes | true |  | Filter value can be NULL |
| DataType | STRING | Yes | No | Yes | @this.DBField.DataType |  | Data type of the field List of possible valuesinteger string text bool date datetime datetimehm timestamp time float JSON |
| DBField | TDBField | Yes | No | Yes |  |  | Database field attached to the control. When specified, the control is database-aware. |
| DefaultRelation | STRING | No | No | Yes |  |  | Default relation List of possible valueseq neq gt gteq lt lteq NULL NOT NULL in notin |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FieldId | STRING | No | No | Yes | id |  |  |
| FieldText | STRING | No | No | Yes | text |  |  |
| FunctionCode | STRING | No | No | Yes |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Label | STRING | Yes | No | Yes | @this.DBField.Label |  | Label text of the control |
| ListFilter | STRING | No | No | Yes |  |  | Source data filtering, URL format |
| ListSource | TQuery | No | No | Yes |  |  | JSON data provider that provides JSON-formatted data to the component |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| Value | STRING | No | No | Yes |  |  | Text value of the control |

### TGridFilter events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TGridFilter methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TGridParameter

*Inheritance: TComponent / TGridControls / TGridParameter*

**Parent must be: TGrid**

TGridParameter

### TGridParameter properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FunctionCode | STRING | No | No | Yes |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |

### TGridParameter events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TGridParameter methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TGridRow

*Inheritance: TComponent / TGridControls / TGridRow*

**Parent must be: TGrid**

TGridRow - handling multiple rows per record

### TGridRow properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Class | STRING | No | No | Yes |  |  | HTML class. Used for injecting extra classes into the control. |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| HideWhenEmpty | BOOLEAN | Yes | No | Yes | false |  |  |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | No |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| ShowColumnHead | BOOLEAN | Yes | No | Yes | false |  |  |
| Style | STRING | No | No | Yes |  |  | Style information |

### TGridRow events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TGridRow methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TIterator

*Inheritance: TComponent / TIterator*

**Parent must be: TComponent**

TIterator

### TIterator properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| DBField | TDBField | No | No | Yes |  |  | Database field attached to the control. When specified, the control is database-aware. |
| DBValue | STRING | No | Yes | Yes |  |  |  |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| JSONDBField | TDBField | No | No | Yes |  |  | Iterate through JSON datafield |
| JSONSource | JSON | No | No | Yes |  |  | Manual JSON source for iterate through |
| ListSource | TQuery | No | No | Yes |  |  | JSON data provider that provides JSON-formatted data to the component |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | No |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| RowCount | NUMBER | No | Yes | Yes |  |  |  |
| SourceURL | STRING | No | No | Yes |  |  | Source URL pointing to the requested resource |
| TotalRowCount | NUMBER | No | Yes | Yes |  |  |  |
| Value | STRING | No | No | Yes |  |  | Text value of the control |

### TIterator events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TIterator methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TJSLib

*Inheritance: TComponent / TJSLib*

**Parent must be: TComponent**

TJSLib

### TJSLib properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | No |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| Placement | STRING | Yes | No | Yes | head |  | Placement List of possible valueshead inplace foot |
| URL | STRING | Yes | No | No |  |  | External URL of the resource |

### TJSLib events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TJSLib methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TLinkedComponent

*Inheritance: TComponent / TLinkedComponent*

**Parent must be: TComponent**

TLinkedComponent

### TLinkedComponent properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Component | TComponent | Yes | No | No |  |  | Just a link to an other object |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Name | STRING | Yes | No | No |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | No |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |

### TLinkedComponent events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TLinkedComponent methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TMapSource

*Inheritance: TComponent / TMapSource*

**Parent must be: TMap**

TMapSource provides ListSource for the Google Maps-based TMap component

### TMapSource properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| AJAXQueueID | STRING | Yes | No | Yes | @this.Name |  |  |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FieldCoords | STRING | No | No | Yes |  |  | DB field contains set of map coordinates |
| FieldIcon | STRING | No | No | Yes |  |  | DB field contains the URL of the map icon |
| FieldId | STRING | Yes | No | Yes | id |  |  |
| FieldInfoWindowContent | STRING | No | No | Yes |  |  |  |
| FieldInfoWindowZIndex | STRING | No | No | Yes |  |  |  |
| FieldLabel | STRING | No | No | Yes |  |  |  |
| FieldLatitude | STRING | Yes | No | Yes | gps_lat |  |  |
| FieldLongitude | STRING | Yes | No | Yes | gps_long |  |  |
| FieldOpacity | STRING | No | No | Yes |  |  |  |
| FieldTitle | STRING | No | No | Yes |  |  |  |
| FieldZIndex | STRING | No | No | Yes |  |  |  |
| FillColor | STRING | No | No | Yes |  |  |  |
| FillOpacity | NUMBER | No | No | Yes |  |  |  |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Label | STRING | No | No | Yes |  |  | Label text of the control |
| ListSource | TQuery | Yes | No | Yes |  |  | JSON data provider that provides JSON-formatted data to the component |
| MapSourceType | STRING | Yes | No | Yes |  |  | List of possible valuesMarkers Polylines Polygons |
| Master | TControl | No | No | Yes |  |  | Used in dependant control situations. Specifies the master component which this control depends on. When master component refreshes its value, this component will also be refreshed. |
| MasterFilterField | STRING | No | No | Yes |  |  |  |
| Name | STRING | No | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| ParentMap | STRING | No | Yes | Yes |  |  | TMap ID of the TMapSource object |
| Refreshing | BOOLEAN | No | Yes | Yes | false |  | List of possible valuesTMapSource loading status |
| StrokeColor | STRING | No | No | Yes |  |  |  |
| StrokeOpacity | NUMBER | No | No | Yes |  |  |  |
| StrokeWeight | NUMBER | No | No | Yes |  |  |  |
| SwapAreaCoords | BOOLEAN | Yes | No | Yes | false |  |  |
| Visible | BOOLEAN | Yes | No | Yes | true |  | When true, control is visible, hidden otherwise |
| ZIndex | NUMBER | No | No | Yes |  |  |  |

### TMapSource events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TMapSource methods

| Name | Ancestor | Description |
| --- | --- | --- |
| refresh() |  | Refreshes component data |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TPAdES

*Inheritance: TComponent / TPAdES*

**Parent must be: TApplication**

TPAdES

### TPAdES properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| Enabled | BOOLEAN | Yes | No | No | true |  | When true (default), control is enabled. Otherwise control is disabled. |
| InputPDFFile | STRING | No | Yes | No |  |  | Input PDF file absolute path and filename |
| Name | STRING | Yes | No | No |  | TComponent | Generic name |
| OutputPDFFile | STRING | No | Yes | No |  |  | Output PDF file absolute path and filename |
| Sender | TComponent | No | Yes | No |  |  | Sender object |

### TPAdES events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |
| onError | PHP |  |  |
| onSignPDF | PHP |  |  |
| onSuccess | PHP |  |  |
| onValidatePDF | PHP |  |  |

### TPAdES methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TParameter

*Inheritance: TComponent / TParameter*

**Parent must be: only inherited types can be placed**

TParameter

### TParameter properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| DevNote | TEXT | No | No | Yes |  | TComponent | Developer Note |
| FunctionCode | STRING | No | No | Yes |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| ParameterName | STRING | Yes | No | Yes |  |  |  |
| Value | STRING | No | No | Yes |  |  | Text value of the control |

### TParameter events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TParameter methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TAPIParameter

*Inheritance: TComponent / TParameter / TAPIParameter*

**Parent must be: TExternalDataProvider**

TAPIParameter

### TAPIParameter properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | No |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| ParameterName | STRING | Yes | No | No |  | TParameter |  |
| Value | STRING | No | No | No |  | TParameter | Text value of the control |

### TAPIParameter events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TAPIParameter methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TDataParameter

*Inheritance: TComponent / TParameter / TDataParameter*

**Parent must be: TComponent**

TDataParameter

### TDataParameter properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| DBField | TDBField | No | No | Yes |  |  | Database field attached to the control. When specified, the control is database-aware. |
| DBValue | STRING | No | Yes | Yes |  |  |  |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FunctionCode | STRING | No | No | Yes |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| ParameterName | STRING | Yes | No | Yes | @this.DBField.FieldName\|@this.Name | TParameter |  |
| Value | STRING | No | No | Yes |  | TParameter | Text value of the control |

### TDataParameter events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TDataParameter methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TDataProxyParameter

*Inheritance: TComponent / TParameter / TDataProxyParameter*

**Parent must be: TDataProxy**

TDataProxyParameter

### TDataProxyParameter properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | No |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| ParameterName | STRING | Yes | No | No | @this.Name | TParameter |  |
| Value | STRING | No | No | No |  | TParameter | Text value of the control |

### TDataProxyParameter events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TDataProxyParameter methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TGlobalParameter

*Inheritance: TComponent / TParameter / TGlobalParameter*

**Parent must be: TComponent**

TGlobalParameter gets its value at render time and set it is global parameter

### TGlobalParameter properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | No |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| ParameterName | STRING | Yes | No | No |  | TParameter |  |
| Value | STRING | No | No | No |  | TParameter | Text value of the control |

### TGlobalParameter events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TGlobalParameter methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TLOVParameter

*Inheritance: TComponent / TParameter / TLOVParameter*

**Parent must be: TLOV**

TLOVParameter

### TLOVParameter properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FunctionCode | STRING | No | No | Yes |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| ParameterName | STRING | Yes | No | Yes |  | TParameter |  |
| Value | STRING | No | No | Yes |  | TParameter | Text value of the control |

### TLOVParameter events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TLOVParameter methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TPartial

*Inheritance: TComponent / TPartial*

**Parent must be: TComponent**

Rendered, result not given back but pushed into ParameterName

### TPartial properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Cacheable | BOOLEAN | Yes | No | Yes | false |  | If not empty, generated content will be cached |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FunctionCode | STRING | No | No | Yes |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| ParameterName | STRING | Yes | No | Yes |  |  |  |

### TPartial events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TPartial methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TQueryFilter

*Inheritance: TComponent / TQueryFilter*

**Parent must be: TComponent**

TQueryFilter

### TQueryFilter properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| DataType | STRING | Yes | No | Yes | text |  | Data type of the field List of possible valuesinteger string text bool date datetime datetimehm timestamp time float JSON |
| DateFormatParameter | STRING | No | No | Yes | @this.datatype |  | DateFormatParameter will be prefixed with PHP\|JS\|DB to convert date\|datetime\|time data types from DBValue to Value and back. Date format is multilingual compatible. Lang parameter will be concatenated. List of possible valuesdate datetime datetimehm dateUniversal |
| DefaultValue | STRING | No | No | Yes |  |  |  |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FieldName | STRING | No | No | Yes |  |  | Field name in the database table |
| FilterGroupParameter | STRING | No | No | No | :filter |  | Filter string in SQL query, defaults to :filter |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| NativeDataType | STRING | No | No | Yes |  |  | List of possible valuesOCI8.BFILE OCI8.BLOB OCI8.CFILE OCI8.CHAR OCI8.CLOB OCI8.DATE OCI8.DATETIME OCI8.MLSLABEL OCI8.NAMEDCOLLECTION OCI8.NCHAR OCI8.NCLOB OCI8.NUMBER OCI8.OBJECT OCI8.OPAQUE OCI8.RAW OCI8.REF OCI8.TABLE OCI8.TIMESTAMP OCI8.TIMESTAMP_DS OCI8.TIMESTAMP_LTZ OCI8.TIMESTAMP_TZ OCI8.TIMESTAMP_YM OCI8.VARCHAR OCI8.VARCHAR2 OCI8.VARRAY PGSQL.ANYARRAY PGSQL.BOOL PGSQL.BYTEA PGSQL.CHAR PGSQL.DATE PGSQL.FLOAT4 PGSQL.FLOAT8 PGSQL.INET PGSQL.INT2 PGSQL.INT2VECTOR PGSQL.INT4 PGSQL.INT8 PGSQL.INTERVAL PGSQL.NAME PGSQL.NUMERIC PGSQL.OID PGSQL.OIDVECTOR PGSQL.PG_DEPENDENCIES PGSQL.PG_LSN PGSQL.PG_MCV_LIST PGSQL.PG_NDISTINCT PGSQL.PG_NODE_TREE PGSQL.REGPROC PGSQL.REGTYPE PGSQL.TEXT PGSQL.TIMESTAMP PGSQL.TIMESTAMPTZ PGSQL.VARCHAR PGSQL.XID JSON.STRING JSON.INTEGER JSON.NUMBER JSON.BOOL JSON.TIMESTAMP JSON.DATE JSON.TIME JSON.DATE-TIME |
| ParameterName | STRING | No | No | Yes |  |  |  |
| RecordSelector | STRING | No | No | Yes | false |  | This filter is used to select a specific record in the query. RecordSelector filters applied only on AutoOpened query-s and usually has default value. List of possible valuesfalse true |
| Relation | STRING | Yes | No | Yes | = |  |  |
| Required | BOOLEAN | Yes | No | Yes | false |  | Control is a required input |
| SQL | TEXT | No | Yes | Yes |  |  | SQL statement |
| Value | STRING | No | No | Yes |  |  | Text value of the control |
| ValueList | BOOLEAN | Yes | No | Yes | false |  | Value is a comma separated list |

### TQueryFilter events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TQueryFilter methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TQueryFilterGroup

*Inheritance: TComponent / TQueryFilterGroup*

**Parent must be: TComponent**

TQueryFilterGroup

### TQueryFilterGroup properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| EmptySQL | TEXT | No | No | Yes |  |  |  |
| FilterGroupParameter | STRING | Yes | No | Yes | :filter |  | Filter string in SQL query, defaults to :filter |
| FunctionCode | STRING | No | No | Yes |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| InternalOperator | STRING | Yes | No | Yes |  |  | List of possible valuesAND OR AND NOT OR NOT |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| SQL | TEXT | No | Yes | Yes |  |  | SQL statement |

### TQueryFilterGroup events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TQueryFilterGroup methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TRoleManager

*Inheritance: TComponent / TRoleManager*

**Parent must be: TApplication**

TRoleManager

### TRoleManager properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| AccessDeniedURL | STRING | No | No | No |  |  | In case of access denied, redirect to URL |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| DisableInitSessionProvider | BOOLEAN | Yes | No | No | false |  | Disable InitSessionProvider in application init phase (in case of proxy is enabled this should be disabled because of the behavior of separate remote calls) |
| Enabled | BOOLEAN | Yes | No | No | @(parameter).Tholos.EnableRoleManager\|true |  | When true (default), control is enabled. Otherwise control is disabled. |
| ErrorMessage | STRING | Yes | No | No |  |  |  |
| FunctionCodes | ARRAY | No | Yes | No |  |  |  |
| InitSessionProvider | TStoredProcedure | No | No | No |  |  |  |
| ListSource | TQuery | No | No | No |  |  | JSON data provider that provides JSON-formatted data to the component |
| LoginID | STRING | No | Yes | No |  |  |  |
| LoginIDHeader | STRING | No | No | No |  |  | Send Rolemanager.loginID in HTTP header with this name |
| LoginURL | STRING | No | No | No |  |  | Redirect URL in case of no user logged in and component has defined function code |
| Name | STRING | No | No | No |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | No |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |

### TRoleManager events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TRoleManager methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TRoute

*Inheritance: TComponent / TRoute*

**Parent must be: TApplication**

TRoute

### TRoute properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| DefaultAction | TAction | No | No | Yes |  |  | Default action of the route when not specified |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FunctionCode | STRING | Yes | No | No | # | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| InitSessionProvider | TStoredProcedure | No | No | Yes |  |  |  |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| PersistentSession | BOOLEAN | Yes | No | No | true |  | If it's false, PHP destroys session on finish |

### TRoute events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TRoute methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TTemplate

*Inheritance: TComponent / TTemplate*

**Parent must be: TAction**

TTemplate

### TTemplate properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| Template | TEMPLATE | Yes | No | Yes |  |  | Template for freestyle formatting :) |

### TTemplate events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TTemplate methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TTimer

*Inheritance: TComponent / TTimer*

**Parent must be: TComponent**

TTimer

### TTimer properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Async | BOOLEAN | Yes | No | No | true |  | Asynchronus call. If false, it does not execute the onExecute trigger and waits RetryInterval msec before the next execution. |
| AutoStart | BOOLEAN | Yes | No | Yes | true |  | Start timer on document ready, otherwise Execute method must called manually. |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| Enabled | BOOLEAN | Yes | No | Yes | true |  | When true (default), control is enabled. Otherwise control is disabled. |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Interval | NUMBER | Yes | No | No | 5000 |  | Interval in milliseconds |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | No |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |
| RetryInterval | NUMBER | Yes | No | No | 100 |  | RetryInterval in milliseconds in synchronous mode. |
| StartInterval | NUMBER | Yes | No | No | @this.Interval |  | First run |
| TimerMode | STRING | Yes | No | Yes | interval |  | interval: run in every Interval msec timeout: run once after Interval msec List of possible valuesinterval timeout |
| WaitOnInactiveTab | BOOLEAN | Yes | No | Yes | true |  |  |

### TTimer events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onExecute | GUI |  |  |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TTimer methods

| Name | Ancestor | Description |
| --- | --- | --- |
| activate() |  |  |
| execute() |  |  |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TWorkflow

*Inheritance: TComponent / TWorkflow*

**Parent must be: TComponent**

TWorkflow

### TWorkflow properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |

### TWorkflow events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TWorkflow methods

| Name | Ancestor | Description |
| --- | --- | --- |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |

## TWorkflowStep

*Inheritance: TComponent / TWorkflowStep*

**Parent must be: TWorkflow**

TWorkflowStep

### TWorkflowStep properties

| Name | Type | Mandatory | Runtime | Hidden data | Default value | Ancestor | Description |
| --- | --- | --- | --- | --- | --- | --- | --- |
| DevNote | TEXT | No | No | No |  | TComponent | Developer Note |
| FunctionCode | STRING | No | No | No |  | TComponent | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Name | STRING | Yes | No | Yes |  | TComponent | Generic name |
| NameSuffix | STRING | No | No | Yes |  | TComponent | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |

### TWorkflowStep events

| Name | Type | Ancestor | Description |
| --- | --- | --- | --- |
| onError | GUI |  |  |
| onExecute | GUI |  |  |
| onResume | GUI |  | onResume triggered when async execution completed |
| onSuccess | GUI |  |  |
| onWait | GUI |  | onWait triggered on async step execution |
| onAfterInit | PHP | TComponent |  |
| onAfterRender | PHP | TComponent |  |
| onBeforeCreate | PHP | TComponent |  |
| onBeforeRender | PHP | TComponent |  |

### TWorkflowStep methods

| Name | Ancestor | Description |
| --- | --- | --- |
| execute() |  |  |
| setDataParameters() | TComponent | Sets data-dataparameters values. If previous values exist they will be merged. |
