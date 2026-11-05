# Tholos :: Component Type documentation

<!-- RELEASE HISTORY START -->
## Release history

- **2026-11-05** — `Tholos_Component_Types-20261105133311.md`
  - Format change: the extract now comes from Tholos Editor as Markdown instead of Tholos Builder HTML. Each type lists only its own properties, events and methods; an "Inherited property overrides" table lists the inherited properties whose settings differ from the parent type.
  - Same 84 component types as `Tholos_Component_Types-20261105105125.html`. A row-level diff against the HTML extract is not possible because of the format change.
- **2026-11-05** — `Tholos_Component_Types-20261105105125.html`
  - `TCheckbox`: new property **`TriState`** (BOOLEAN, mandatory, hidden, default `false`) — "Tri state checkbox". Enables a third, indeterminate (NULL) state the user can select by clicking: unchecked → checked → indeterminate → unchecked.
  - `TCheckbox`: new property **`ValueNull`** (STRING, hidden) — "In case of tri-state mode the value represents the indeterminated state"; empty by default, so the hidden input submits an empty string (NULL).
  - `TCheckbox`: new event **`onIndeterminate`** (GUI) — fires when the user switches the checkbox to the indeterminate state in tri-state mode.
- **2026-10-18** — `Tholos_Component_Types-20261018223525.html`
  - `TGrid`: **`ScrollOnlyWhenFocused`** — *Hidden data* flag corrected Yes→No.
- **2026-10-18** — `Tholos_Component_Types-20261018221320.html`
  - `TGrid`: new property **`ScrollOnlyWhenFocused`** (BOOLEAN, mandatory, hidden, default `false`) — "Scroll only when focused (active)". Effective only together with `ScrollableY=true`: the data container gets `TGrid-scroll-on-focus` (`overflow-y: hidden`), so the mouse wheel scrolls the page; a pointerdown inside activates the grid's own scrolling (`TGrid-scroll-active`, with `overscroll-behavior: contain`), leaving the grid or clicking elsewhere releases it. A `fa-bullseye-pointer` badge in the top-right corner marks inactive grids. Wired in `TGrid.partial.table.head.template`, `TGrid.css`, `TGrid.js`; checked as `=true` for backward compatibility.
- **2026-08-05** — `Tholos_Component_Types-20260805111549.html`
  - `TDataProvider`: new property **`AutoOpenAlwaysRequired`** (BOOLEAN, runtime, hidden, default `false`) — "In Optimized mode it forces the application to automatically opens the component". Inherited by every provider and consulted by `TholosApplication::autoOpen()` *before* the `Optimized` reference check, so a provider that exists for a request-level side effect can opt out of the render-scope decision entirely.
  - `TFileProcessor`: inherits **`AutoOpenAlwaysRequired`** with default **`true`** (not hidden) — it is driven by `$_FILES` rather than by anything being rendered, and its `TFileUpload` control usually lives in another route, so `Optimized` mode would otherwise skip it and uploads would silently do nothing.
  - `TExternalDataProvider`, `TQuery`, `TQueryGroup`, `TStoredProcedure`, `TAPIPost`: inherit **`AutoOpenAlwaysRequired`** with default `false` (not hidden).
  - `TJSONDataProvider`: inherits **`AutoOpenAlwaysRequired`** with default `false` (hidden).
  - `TQueryGroup`: **`AutoOpenAllowed`** — *Hidden data* flag changed Yes→No.
- **2026-07-21** — `Tholos_Component_Types-20260721200536.html`
  - `TGrid`: new event **`onMasterValueChanged`** (GUI) — "Triggers when master value changed on a detail grid". Wired in `TGrid.js` (jQuery trigger at the three `masterDataChange` change-guards) and `TGrid.partial.foot.template`; the handler receives the new master value.
- **2026-07-06** — `Tholos_Component_Types-20260706144817.html`
  - `TWorkflow`: new property **`Parallel`** (BOOLEAN, mandatory, default `false`) — "Execute all belonging TWorkFlowStep parallel". When `true`, the workflow's frontend `execute()` fires every child `TWorkflowStep`'s `onExecute` in document order without waiting between them; when `false`, steps stay developer-driven.
  - `TWorkflow`: new method **`execute()`** — frontend workflow entry point.
  - `TGrid`: new property **`MultiSelectValue`** (STRING) — "MultiSelect checkbox value".
  - `TGrid`: new methods **`clearSelection()`** ("Clears selection") and **`setValuesRefresh()`** ("Set selection values and refresh").
  - `TGrid`: **`MultiSelectDBField`** — *Hidden data* flag changed Yes→No (now emitted as a `data-` attribute).
- **2026-05-11** — `Tholos_Component_Types-20260511111444.html`
  - `TGrid`: new property **`MultiSelectDBField`** (TDBField, runtime, hidden) — value of the row checkbox when grid is MultiSelect; empty hides the checkbox.
  - `TGrid`: new method **`setValues()`** — sets multiple values (checked items) on a multi-select grid.
  - `TFormControl`: `Enabled` default value now documented as `true`.
  - `TFormControl`: `Label` default value now documented as `@this.DBField.Label`.
- **2026-04-29** — `Tholos_Component_Types-20260429124455.html` (baseline)
  - Initial release history baseline. 84 documented component types. See the file body for the full reference.

<!-- RELEASE HISTORY END -->

How to read this file:
- Each type lists only its own properties, events and methods. A type also has every member of the types in its Inheritance line, so collect members along that chain.
- "Inherited property overrides" lists the inherited properties whose settings this type changes compared to its parent type. A disabled property is not available on that type.
- "Parent must be" names the component type that an instance of this type must be placed under.

## Inheritance tree

- TComponent
  - TAction
  - TApplication
  - TCacheArrayItem
  - TCacheArrayRenderer
  - TCacheArraySave
  - TConfirmButton
  - TConfirmDialog
  - TContainer
    - TCell
    - TColumn
    - TForm
    - TFormContainer
    - TGridRowActions
    - TModal
    - TPage
      - TPDFPage
    - TTabPane
    - TTabs
    - TWidget
    - TWizard
    - TWizardStep
  - TControl
    - TButton
      - TButtonDropdown
    - TFormControl
      - TCheckbox
      - TDateTimePicker
      - TEdit
      - TFileUpload
      - THTMLEdit
      - THTMLViewer
      - TLabel
      - TLOV
      - TRadio
      - TStatic
      - TText
    - TGrid
    - THidden
    - TImage
    - TLink
      - TButtonDropdownItem
      - TMenuItem
    - TMap
  - TDataProvider
    - TExternalDataProvider
    - TFileProcessor
    - TJSONDataProvider
    - TQuery
    - TQueryGroup
    - TStoredProcedure
      - TAPIPost
  - TDataProxy
  - TDBField
    - TJSONField
  - TDBParam
  - TDiagramEditor
  - TDocumentTitle
  - TDPOpen
  - TGridControls
    - TGridColumn
    - TGridFilter
    - TGridParameter
    - TGridRow
  - TIterator
  - TJSLib
  - TLinkedComponent
  - TMapSource
  - TPAdES
  - TParameter
    - TAPIParameter
    - TDataParameter
    - TDataProxyParameter
    - TGlobalParameter
    - TLOVParameter
  - TPartial
  - TQueryFilter
  - TQueryFilterGroup
  - TRoleManager
  - TRoute
  - TTemplate
  - TTimer
  - TWorkflow
  - TWorkflowStep

## Application structure

- TApplication
  - TDataProxy
    - TDataProxyParameter
  - TPAdES
  - TPage
  - TPDFPage
  - TRoleManager
  - TRoute
    - TAction
      - TConfirmDialog
        - TConfirmButton
      - TFileProcessor
      - TModal
      - TTemplate
    - TAPIPost
    - TExternalDataProvider
      - TAPIParameter
    - TQuery
    - TQueryGroup
    - TStoredProcedure

## Visual component structure

- TComponent
  - TCacheArrayItem
  - TCacheArrayRenderer
  - TCacheArraySave
  - TCell
  - TColumn
  - TContainer
    - TButton
    - TButtonDropdown
      - TButtonDropdownItem
    - TCheckbox
    - TDateTimePicker
    - TDiagramEditor
    - TEdit
    - TFileUpload
    - TForm
    - TGrid
      - TGridFilter
      - TGridParameter
      - TGridRow
    - THidden
    - THTMLEdit
    - THTMLViewer
    - TImage
    - TLink
    - TLOV
      - TLOVParameter
    - TMap
      - TMapSource
    - TRadio
    - TStatic
    - TTabs
      - TTabPane
    - TText
    - TWidget
    - TWizard
      - TWizardStep
  - TDataParameter
  - TDocumentTitle
  - TDPOpen
  - TFormContainer
  - TGlobalParameter
  - TGridColumn
  - TGridRowActions
  - TIterator
  - TJSLib
  - TLabel
  - TLinkedComponent
  - TMenuItem
  - TPartial
  - TQueryFilter
  - TQueryFilterGroup
  - TTimer
  - TWorkflow
    - TWorkflowStep

# Component types

## TComponent
- Inheritance: TComponent
- Parent must be: only inherited types can be placed
- Description: TComponent is the father of all Tholos components. Super minimalistic, only defines few basic properties, methods and events.

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| DevNote | TEXT | No | No | No |  |  | Developer Note |
| FunctionCode | STRING | No | No | No |  |  | Function Code for Role management, use # in case of function needs logged user only (without any role) |
| Name | STRING | Yes | No | Yes |  |  | Generic name |
| NameSuffix | STRING | No | No | No |  |  | Value of Name property will be suffixed with this value. Use ++ sign to concatenation, ex: "_++@this.DBField.Value" |

### Events
| Name | Type | Description |
|---|---|---|
| onAfterInit | PHP |  |
| onAfterRender | PHP |  |
| onBeforeCreate | PHP |  |
| onBeforeRender | PHP |  |

### Methods
| Name | Description |
|---|---|
| setDataParameters() | Sets data-dataparameters values. If previous values exist they will be merged. |

## TAction
- Inheritance: TComponent > TAction
- Parent must be: TRoute
- Description: TAction

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| HTTPMethod | STRING | No | No | Yes |  | GET, HEAD, POST, PUT, DELETE, TRACE, OPTIONS, CONNECT, PATCH | HTTP method that the action will handle |
| Page | TPage | No | No | No |  |  |  |
| ResponseType | STRING | Yes | No | Yes | HTML | HTML, JSON, JSONDATA, PLAINTEXT, XML, NONE | Type of the generated response. HTML will render a full HTML5-compliant page while HTMLSnippet will only render a partial useful for AJAX page generators. JSON and PLAINTEXT are self-explanatory. |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| FunctionCode | TComponent | Yes | No | No |  | No |

### Methods
| Name | Description |
|---|---|
| navigate() | Navigate to this action |

## TApplication
- Inheritance: TComponent > TApplication
- Parent must be: must be a root element of the application structure
- Description: TApplication

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| FunctionCode | TComponent | No | No | Yes |  | Yes |
| NameSuffix | TComponent | No | No | Yes |  | No |

### Events
| Name | Type | Description |
|---|---|---|
| onError | PHP |  |

## TCacheArrayItem
- Inheritance: TComponent > TCacheArrayItem
- Parent must be: TComponent
- Description: TCacheArray stores rendered items in cache

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| CacheID | STRING | Yes | No | No |  |  | Cache ID |
| CacheInfo | ARRAY | No | Yes | Yes |  |  | Cache info |
| CacheRefresh | BOOLEAN | No | Yes | Yes |  |  | Force cache to refresh |
| CacheUsed | BOOLEAN | No | Yes | Yes |  |  | Cache is used for reading data |
| IndexField | TDBField | Yes | No | No |  |  | Field used as index in array |
| OrderField | TDBField | No | No | No |  |  | Render by its order value |
| SyncField | TDBField | No | No | No |  |  | Field that shows the record has been changed |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| FunctionCode | TComponent | No | No | No |  | Yes |
| Name | TComponent | Yes | No | No |  | No |

## TCacheArrayRenderer
- Inheritance: TComponent > TCacheArrayRenderer
- Parent must be: TComponent
- Description: TCacheArrayRenderer - renders an array from the cache stored by a TCacheArray component

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| CacheID | STRING | Yes | No | No |  |  | Cache ID |
| CacheInfo | ARRAY | No | Yes | No |  |  | Cache info |
| CacheRefresh | BOOLEAN | No | Yes | No |  |  | Force cache to refresh |
| CacheSelectionMode | STRING | Yes | No | No | All | All, NewItems, NewOrModifiedItems, ModifiedItems, SyncedItems |  |
| CacheUsed | BOOLEAN | No | Yes | No |  |  | Cache is used for reading data |
| Enabled | BOOLEAN | Yes | No | No | true |  | When true (default), control is enabled. Otherwise control is disabled. |
| PurgeAt | STRING | Yes | No | No | SameRenderPhase | SameRenderPhase, DifferentRenderPhase, EveryRenderPhase, Never |  |
| PurgeNotRenderedItems | BOOLEAN | Yes | No | No | false |  | In case it is true and an item is not rendered from the array it will be purged |
| Sorting | STRING | Yes | No | No | NONE | ASC, DESC, NONE | Order result by TCacheArrayItems OrderField value |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| FunctionCode | TComponent | No | No | No |  | Yes |
| Name | TComponent | Yes | No | No |  | No |

## TCacheArraySave
- Inheritance: TComponent > TCacheArraySave
- Parent must be: TComponent
- Description: TCacheArraySave

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| CacheID | STRING | Yes | No | No |  |  | Cache ID |
| CacheValidity | NUMBER | No | No | No |  |  | Cache validity in minutes |
| Enabled | BOOLEAN | Yes | No | No | true |  | When true (default), control is enabled. Otherwise control is disabled. |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| FunctionCode | TComponent | No | No | No |  | Yes |
| Name | TComponent | Yes | No | No |  | No |

## TConfirmButton
- Inheritance: TComponent > TConfirmButton
- Parent must be: TConfirmDialog
- Description: TConfirmButton

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| Class | STRING | No | No | No |  |  | HTML class. Used for injecting extra classes into the control. |
| Label | STRING | Yes | No | Yes | [:test,Y:] |  | Label text of the control |
| Value | STRING | No | No | Yes |  |  | Text value of the control |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| NameSuffix | TComponent | No | No | Yes |  | No |

### Events
| Name | Type | Description |
|---|---|---|
| onClick | GUI | onClick event |

## TConfirmDialog
- Inheritance: TComponent > TConfirmDialog
- Parent must be: TAction
- Description: TConfirmDialog

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| Backdrop | BOOLEAN | Yes | No | Yes | true |  |  |
| ConfirmType | STRING | Yes | No | Yes | YesNo | YesNo, OKCancel, OK, YesNoCancel, Custom |  |
| Enabled | BOOLEAN | Yes | No | Yes | true |  | When true (default), control is enabled. Otherwise control is disabled. |
| Label | STRING | Yes | No | Yes |  |  | Label text of the control |
| Title | STRING | Yes | No | Yes |  |  |  |
| Value | STRING | No | Yes | Yes |  |  | Text value of the control |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| NameSuffix | TComponent | No | No | Yes |  | No |

### Events
| Name | Type | Description |
|---|---|---|
| onClickCANCEL | GUI |  |
| onClickNO | GUI |  |
| onClickOK | GUI |  |
| onClickYES | GUI |  |
| onDisabled | GUI |  |
| onHide | GUI | Fired when component transitions from shown to hidden |
| onShow | GUI | Fired when component transitions from hidden to shown |

### Methods
| Name | Description |
|---|---|
| show() |  |

## TContainer
- Inheritance: TComponent > TContainer
- Parent must be: TComponent
- Description: Container element

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| AJAXQueueID | STRING | Yes | No | Yes | @this.Name |  |  |
| Attributes | TEXT | No | No | No |  |  | HTML Special attributes |
| Class | STRING | No | No | No |  |  | HTML class. Used for injecting extra classes into the control. |
| ContainerType | STRING | Yes | No | No | div | div, li, ul, ol, tr, td, tbody, thead, tfoot, table, span, nav, section, header, footer, aside |  |
| DoNotAddCloseTag | BOOLEAN | Yes | No | No | false |  | Do not add HTML close tag to container |
| Generate | BOOLEAN | Yes | No | No | true |  |  |
| Loadable | BOOLEAN | Yes | No | Yes | true |  | Allow content to be load via loadContent method |
| SourceURL | STRING | No | No | Yes |  |  | Source URL pointing to the requested resource |
| Style | STRING | No | No | No |  |  | Style information |
| Visible | BOOLEAN | Yes | No | Yes | true |  | When true, control is visible, hidden otherwise |

### Events
| Name | Type | Description |
|---|---|---|
| onClick | GUI | onClick event |
| onContentCleared | GUI |  |
| onContentLoaded | GUI | Fired after loadContent successfully executed |
| onHide | GUI | Fired when component transitions from shown to hidden |
| onReady | GUI | triggers automatically on page generated |
| onShow | GUI | Fired when component transitions from hidden to shown |

### Methods
| Name | Description |
|---|---|
| clearContent() | Clears its content |
| hide() |  |
| loadContent() | Loads a content from extrenal URL received in sourceURL parameter |
| setEnabled() | Set component enabled/disabled |
| setLoadable() | switch Loadable property to true or false |
| setVisible() |  |
| show() |  |

## TCell
- Inheritance: TComponent > TContainer > TCell
- Parent must be: TComponent
- Description: TCell

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| Position | STRING | Yes | No | No | absolute | static, relative, fixed, absolute, sticky |  |
| PositionBottom | STRING | No | No | No |  |  |  |
| PositionHeight | STRING | No | No | No | auto |  |  |
| PositionLeft | STRING | No | No | No |  |  |  |
| PositionRight | STRING | No | No | No |  |  |  |
| PositionTop | STRING | No | No | No |  |  |  |
| PositionWidth | STRING | No | No | No | auto |  |  |

## TColumn
- Inheritance: TComponent > TContainer > TColumn
- Parent must be: TComponent
- Description: TColumn

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| OffsetExtraSmall | NUMBER | No | No | No |  | 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 | Bootstrap offsets - col-xs-offset-* |
| OffsetLarge | NUMBER | No | No | No |  | 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 | Bootstrap offsets - col-lg-offset-* |
| OffsetMedium | NUMBER | No | No | No |  | 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 | Bootstrap offsets - col-md-offset-* |
| OffsetSmall | NUMBER | No | No | No |  | 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 | Bootstrap offsets - col-sm-offset-* |
| SizeExtraSmall | NUMBER | No | No | No |  | 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 | Bootstrap column size - col-xs-* |
| SizeLarge | NUMBER | No | No | No |  | 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 | Bootstrap column size - col-lg-* |
| SizeMedium | NUMBER | No | No | No |  | 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 | Bootstrap column size - size-md-* |
| SizeSmall | NUMBER | No | No | No |  | 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 | Bootstrap column size - col-sm-* |
| SurroundWithRow | BOOLEAN | Yes | No | No | false |  | Surround column by a bootstrap row |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| ContainerType | TContainer | No | No | Yes |  | Yes |
| DoNotAddCloseTag | TContainer | No | No | Yes |  | Yes |
| NameSuffix | TComponent | No | No | Yes |  | No |

## TForm
- Inheritance: TComponent > TContainer > TForm
- Parent must be: TContainer
- Description: TForm

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| Action | TAction | No | No | Yes |  |  | Action that this component runs |
| CloseModalOnSuccess | BOOLEAN | Yes | No | Yes | true |  | Close parent modal container after successfull submit |
| ConfirmDialog | TConfirmDialog | No | No | Yes |  |  |  |
| ControlSizeExtraSmall | NUMBER | No | No | No |  | 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 |  |
| ControlSizeLarge | NUMBER | No | No | No |  | 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 |  |
| ControlSizeMedium | NUMBER | No | No | No | 8 | 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 |  |
| ControlSizeSmall | NUMBER | No | No | No |  | 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 |  |
| EnableAfterSubmit | BOOLEAN | Yes | No | Yes | false |  | Set form enabled after submit |
| EncodingType | STRING | No | No | Yes |  |  | EncodingType attribute specifies how the form-data should be encoded when submitting it to the server. Can only be used when the form method is POST. |
| FormState | STRING | No | Yes | Yes |  | VALIDATION, SUBMIT |  |
| LabelSizeExtraSmall | NUMBER | No | No | No |  | 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 | Visual control label size for extra small screens. |
| LabelSizeLarge | NUMBER | No | No | No |  | 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 | Visual control label size for large screens. |
| LabelSizeMedium | NUMBER | No | No | No | 4 | 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 | Visual control label size for medium screens. |
| LabelSizeSmall | NUMBER | No | No | No |  | 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 | Visual control label size for small screens. |
| Method | STRING | No | No | Yes |  | POST, GET |  |
| Submitter | TDataProvider | No | No | Yes |  |  | Dataprovider which receives submitted data |
| Target | STRING | No | No | Yes |  |  | HTML Target property |
| URL | STRING | No | No | Yes |  |  | External URL of the resource |
| ValidateOnInit | BOOLEAN | Yes | No | Yes | true |  | Execute validator with formState=INIT |
| Validator | TDataProvider | No | No | Yes |  |  |  |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| Class | TContainer | No | No | No | form-horizontal | No |
| ContainerType | TContainer | No | No | Yes |  | Yes |
| DoNotAddCloseTag | TContainer | No | No | Yes |  | Yes |
| Generate | TContainer | Yes | No | Yes | true | No |
| SourceURL | TContainer | No | No | Yes |  | Yes |

### Events
| Name | Type | Description |
|---|---|---|
| onBeforeSubmit | GUI |  |
| onCancel | GUI |  |
| onErrorAlert | GUI |  |
| onSubmitError | GUI |  |
| onSubmitSuccess | GUI |  |
| onSuccessAlert | GUI |  |
| onValidateError | GUI |  |
| onValidateSuccess | GUI |  |

### Methods
| Name | Description |
|---|---|
| cancel() |  |
| submit() |  |
| validate() |  |

## TFormContainer
- Inheritance: TComponent > TContainer > TFormContainer
- Parent must be: TComponent
- Description: TFormContainer

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| ClearContentOnHide | BOOLEAN | Yes | No | Yes | true |  |  |
| OverrideFormEvents | BOOLEAN | Yes | No | Yes | true |  |  |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| DoNotAddCloseTag | TContainer | Yes | No | Yes | false | No |
| NameSuffix | TComponent | No | No | Yes |  | No |
| Style | TContainer | No | No | Yes |  | No |

### Events
| Name | Type | Description |
|---|---|---|
| onErrorAlert | GUI |  |
| onSubmitSuccess | GUI |  |
| onSuccessAlert | GUI |  |

## TGridRowActions
- Inheritance: TComponent > TContainer > TGridRowActions
- Parent must be: TComponent
- Description: TGridRowActions

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| Align | STRING | Yes | No | No | right | left, center, right | Content alignment |
| ColumnOffset | NUMBER | Yes | No | No | 0 |  |  |
| ColumnSpan | NUMBER | No | No | No |  |  | ColumnSpan in table |
| Label | STRING | Yes | No | No | Művelet |  | Label text of the control |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| AJAXQueueID | TContainer | No | No | Yes |  | Yes |
| ContainerType | TContainer | No | No | Yes |  | Yes |
| DoNotAddCloseTag | TContainer | No | No | Yes |  | Yes |
| Name | TComponent | No | No | Yes |  | No |

## TModal
- Inheritance: TComponent > TContainer > TModal
- Parent must be: TAction
- Description: TModal - Modal popup

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| ClearContentOnHide | BOOLEAN | Yes | No | Yes | true |  |  |
| OverrideFormEvents | BOOLEAN | Yes | No | Yes | true |  |  |
| ShowClose | BOOLEAN | Yes | No | No | trtue |  | Show the "Close" button in the icon toolbar. |
| Static | BOOLEAN | Yes | No | Yes | true |  |  |
| Title | STRING | No | No | No |  |  |  |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| ContainerType | TContainer | No | No | Yes |  | Yes |
| DoNotAddCloseTag | TContainer | No | No | Yes |  | Yes |
| NameSuffix | TComponent | No | No | Yes |  | No |
| Visible | TContainer | No | No | Yes |  | Yes |

### Events
| Name | Type | Description |
|---|---|---|
| onErrorAlert | GUI |  |
| onSubmitSuccess | GUI |  |
| onSuccessAlert | GUI |  |

## TPage
- Inheritance: TComponent > TContainer > TPage
- Parent must be: TApplication
- Description: TPage

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| CSS | TEXT | No | No | No |  |  | CSS |
| Template | TEMPLATE | Yes | No | No |  |  | Template for freestyle formatting :) |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| AJAXQueueID | TContainer | No | No | Yes | @this.Name | Yes |
| ContainerType | TContainer | No | No | Yes |  | Yes |
| DoNotAddCloseTag | TContainer | No | No | Yes |  | Yes |
| Generate | TContainer | No | No | Yes |  | Yes |
| Visible | TContainer | No | No | Yes |  | Yes |

## TPDFPage
- Inheritance: TComponent > TContainer > TPage > TPDFPage
- Parent must be: TApplication
- Description: TPDFPage

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| BodyContainerName | STRING | Yes | No | Yes |  |  | Page body container |
| CSSFile | STRING | No | No | Yes |  |  |  |
| Download | BOOLEAN | Yes | No | No | false |  | Download PDF |
| FileName | STRING | No | No | No |  |  | Filename of generated PDF |
| FooterContainerName | STRING | No | No | Yes |  |  | Footer container component |
| HeaderContainerName | STRING | No | No | No |  |  | Header container component |
| PAdES | TPAdES | No | No | Yes |  |  |  |
| PDFConfig | JSON | Yes | No | No | {"mode":"A4"} |  | mPDF configuration |
| Watermark | STRING | No | No | Yes |  |  | Watermark |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| AJAXQueueID | TContainer | No | No | Yes |  | Yes |
| CSS | TPage | No | No | Yes |  | No |
| SourceURL | TContainer | No | No | No |  | No |

## TTabPane
- Inheritance: TComponent > TContainer > TTabPane
- Parent must be: TTabs
- Description: TTabPane

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| FirstLoad | BOOLEAN | No | Yes | Yes | true |  | Runtime property indicating first appearance of the component |
| Icon | STRING | No | No | No |  |  | Icon specified in the icon's library format, eg. "fa fa-share" or "glyphicon glyphicon-share" |
| IconAlign | STRING | No | No | No |  | left, right | Alignment of the icon. Left means before the component's label or content, right means after it. Defaults to left. |
| Label | STRING | No | No | Yes |  |  | Label text of the control |
| LabelClass | STRING | No | No | No |  |  |  |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| ContainerType | TContainer | No | No | Yes |  | Yes |
| DoNotAddCloseTag | TContainer | No | No | Yes |  | Yes |
| FunctionCode | TComponent | Yes | No | No |  | No |

### Events
| Name | Type | Description |
|---|---|---|
| onActivate | GUI | Fired when component has been activated |
| onDeactivate | GUI | Fired when component is deactivated |

### Methods
| Name | Description |
|---|---|
| activate() |  |
| setLabel() | Sets the label text of a component |

## TTabs
- Inheritance: TComponent > TContainer > TTabs
- Parent must be: TContainer
- Description: TTabs

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| ContainerClass | STRING | No | No | No |  |  |  |
| DefaultTabPane | TTabPane | No | No | Yes |  |  | Default tab pane to show |
| HeaderClass | STRING | No | No | No |  |  | Class of the component's header |
| RememberTabPane | BOOLEAN | Yes | No | No | true |  |  |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| Class | TContainer | No | No | Yes |  | Yes |
| ContainerType | TContainer | No | No | Yes |  | Yes |
| DoNotAddCloseTag | TContainer | No | No | Yes |  | Yes |
| NameSuffix | TComponent | No | No | Yes |  | No |
| Style | TContainer | No | No | Yes |  | Yes |

### Events
| Name | Type | Description |
|---|---|---|
| onAfterTabShow | GUI |  |
| onBeforeTabShow | GUI |  |

## TWidget
- Inheritance: TComponent > TContainer > TWidget
- Parent must be: TContainer
- Description: TWidget

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| Collapsible | STRING | Yes | No | No | false | false, InitialClosed, InitialOpen | Defines whether the component can be collapsed and if so, what its initial state |
| Disposable | BOOLEAN | Yes | No | No | false |  | Defines whether the component can be disposed |
| HeaderClass | STRING | No | No | No |  |  | Class of the component's header |
| HeaderControlsParameter | STRING | No | No | No |  |  |  |
| Icon | STRING | No | No | No |  |  | Icon specified in the icon's library format, eg. "fa fa-share" or "glyphicon glyphicon-share" |
| IconAlign | STRING | No | No | No |  | left, right | Alignment of the icon. Left means before the component's label or content, right means after it. Defaults to left. |
| Label | STRING | No | No | Yes |  |  | Label text of the control |
| ShowHeader | BOOLEAN | Yes | No | Yes | false |  | Show widget header |
| TitleClass | STRING | No | No | Yes |  |  |  |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| ContainerType | TContainer | No | No | Yes |  | Yes |
| DoNotAddCloseTag | TContainer | No | No | Yes |  | Yes |
| NameSuffix | TComponent | No | No | Yes |  | No |

### Events
| Name | Type | Description |
|---|---|---|
| onCollapse | GUI | Component is being collapsed |
| onExpand | GUI | Component is being expanded |

## TWizard
- Inheritance: TComponent > TContainer > TWizard
- Parent must be: TContainer
- Description: TWizard

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| BtnFinishLabel | STRING | Yes | No | No | [:WIZARD.BUTTON.FINISH,Véglegesítés:] |  |  |
| BtnNextClass | STRING | Yes | No | No | btn-default btn-sm |  |  |
| BtnNextIcon | STRING | Yes | No | No | fa fa-angle-right |  |  |
| BtnNextLabel | STRING | Yes | No | No | [:WIZARD.BUTTON.NEXT,Következő:] |  |  |
| BtnPrevClass | STRING | Yes | No | No | btn-default btn-sm |  |  |
| BtnPrevIcon | STRING | Yes | No | No | fa fa-angle-left |  |  |
| BtnPrevLabel | STRING | Yes | No | No | [:WIZARD.BUTTON.PREV,Előző:] |  |  |
| DefaultWizardStep | TWizardStep | No | No | Yes |  |  |  |
| DisableBackButton | BOOLEAN | Yes | No | Yes | false |  |  |
| RememberWizardStep | BOOLEAN | Yes | No | Yes | true |  |  |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| ContainerType | TContainer | No | No | Yes |  | Yes |
| DoNotAddCloseTag | TContainer | No | No | Yes |  | Yes |
| Generate | TContainer | Yes | No | Yes | true | No |
| Name | TComponent | No | No | Yes |  | No |
| NameSuffix | TComponent | No | No | Yes |  | No |

### Events
| Name | Type | Description |
|---|---|---|
| onAfterStepShow | GUI |  |
| onBeforeStepShow | GUI |  |
| onWizardComplete | GUI |  |

### Methods
| Name | Description |
|---|---|
| first() |  |
| moveTo() |  |
| next() |  |
| previous() |  |

## TWizardStep
- Inheritance: TComponent > TContainer > TWizardStep
- Parent must be: TWizard
- Description: TWizardStep

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| Label | STRING | Yes | No | Yes |  |  | Label text of the control |
| Sequence | NUMBER | Yes | No | Yes |  |  |  |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| ContainerType | TContainer | No | No | Yes |  | Yes |
| DoNotAddCloseTag | TContainer | No | No | Yes |  | Yes |
| Name | TComponent | No | No | Yes |  | No |
| NameSuffix | TComponent | No | No | Yes |  | No |

## TControl
- Inheritance: TComponent > TControl
- Parent must be: only inherited types can be placed
- Description: TControl

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| Attributes | TEXT | No | No | Yes |  |  | HTML Special attributes |
| Class | STRING | No | No | Yes |  |  | HTML class. Used for injecting extra classes into the control. |
| DBField | TDBField | No | No | Yes |  |  | Database field attached to the control. When specified, the control is database-aware. |
| DBParameterName | STRING | No | No | Yes |  |  |  |
| DBValue | STRING | No | Yes | Yes |  |  |  |
| Enabled | BOOLEAN | Yes | No | Yes |  |  | When true (default), control is enabled. Otherwise control is disabled. |
| HelpText | TEXT | No | No | Yes |  |  | Help text to be displayed for the control |
| Label | STRING | No | No | Yes |  |  | Label text of the control |
| LabelClass | STRING | No | No | Yes |  |  |  |
| ReadOnly | BOOLEAN | No | No | Yes | false | false, true |  |
| Style | STRING | No | No | Yes |  |  | Style information |
| Value | STRING | No | No | Yes |  |  | Text value of the control |
| Visible | BOOLEAN | Yes | No | Yes |  |  | When true, control is visible, hidden otherwise |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| FunctionCode | TComponent | No | No | Yes |  | No |
| NameSuffix | TComponent | No | No | Yes |  | No |

### Events
| Name | Type | Description |
|---|---|---|
| onAfterCreate | GUI |  |
| onBeforeClick | GUI | Before click event |
| onBeforeCreate | GUI |  |
| onBlur | GUI | Fired when a user leaves an input field |
| onChange | GUI | Value change event |
| onClick | GUI | onClick event |
| onConfirmFalse | GUI |  |
| onConfirmTrue | GUI |  |
| onHide | GUI | Fired when component transitions from shown to hidden |
| onMouseDown | GUI |  |
| onMouseOut | GUI |  |
| onMouseOver | GUI |  |
| onMouseUp | GUI |  |
| onShow | GUI | Fired when component transitions from hidden to shown |

### Methods
| Name | Description |
|---|---|
| parseControlParameters() | Parses and executes a number of control parameters |
| setEnabled() | Set component enabled/disabled |
| setLabel() | Sets the label text of a component |
| setReadOnly() |  |
| setRequired() |  |
| setValue() |  |
| setVisible() |  |

## TButton
- Inheritance: TComponent > TControl > TButton
- Parent must be: TContainer
- Description: TButton

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| BootstrapSize | STRING | No | No | No |  | xs, sm, lg | Twitter Bootstrap compliant standard control size as follows: xs (extra small), sm (small) and lg (large). When not specified control has normal size. |
| ConfirmDialog | TConfirmDialog | No | No | Yes |  |  |  |
| Icon | STRING | No | No | No |  |  | Icon specified in the icon's library format, eg. "fa fa-share" or "glyphicon glyphicon-share" |
| IconAlign | STRING | No | No | No |  | left, right | Alignment of the icon. Left means before the component's label or content, right means after it. Defaults to left. |
| MiddleClick | BOOLEAN | Yes | No | No | false |  | Enable middle click support on component's click event. |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| Class | TControl | No | No | No |  | No |
| DBField | TControl | No | No | Yes |  | Yes |
| DBParameterName | TControl | No | No | Yes |  | Yes |
| DBValue | TControl | No | No | Yes |  | Yes |
| Enabled | TControl | Yes | No | Yes | true | No |
| FunctionCode | TComponent | Yes | No | No |  | No |
| HelpText | TControl | No | No | No |  | No |
| Label | TControl | No | No | No |  | No |
| LabelClass | TControl | No | No | No |  | No |
| ReadOnly | TControl | No | No | Yes |  | Yes |
| Style | TControl | No | No | No |  | No |
| Value | TControl | No | No | Yes |  | Yes |
| Visible | TControl | Yes | No | Yes | true | No |

### Methods
| Name | Description |
|---|---|
| click() | Click method |

## TButtonDropdown
- Inheritance: TComponent > TControl > TButton > TButtonDropdown
- Parent must be: TContainer
- Description: TButtonDropdown

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| DropAlign | STRING | Yes | No | No | end | end, begin |  |
| DropDirection | STRING | No | No | No |  | dropdown, dropup |  |
| DropdownClass | STRING | No | No | No |  |  |  |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| FunctionCode | TComponent | No | No | No |  | No |
| IconAlign | TButton | No | No | No | left | No |
| MiddleClick | TButton | No | Yes | No | false | No |
| ReadOnly | TControl | Yes | No | Yes | false | No |

## TFormControl
- Inheritance: TComponent > TControl > TFormControl
- Parent must be: must be a root element of the application structure
- Description: TFormControl

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| ControlSizeExtraSmall | NUMBER | No | No | Yes |  | 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 |  |
| ControlSizeLarge | NUMBER | No | No | Yes |  | 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 |  |
| ControlSizeMedium | NUMBER | No | No | Yes | 8 | 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 |  |
| ControlSizeSmall | NUMBER | No | No | Yes |  | 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 |  |
| LabelSizeExtraSmall | NUMBER | No | No | Yes |  | 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 | Visual control label size for extra small screens. |
| LabelSizeLarge | NUMBER | No | No | Yes |  | 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 | Visual control label size for large screens. |
| LabelSizeMedium | NUMBER | No | No | Yes | 4 | 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 | Visual control label size for medium screens. |
| LabelSizeSmall | NUMBER | No | No | Yes |  | 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 | Visual control label size for small screens. |
| PlaceHolder | STRING | No | No | Yes |  |  | When control value is not specified, placeholder text will be displayed. Not supported by all controls. |
| Required | BOOLEAN | No | No | Yes |  |  | Control is a required input |
| RowClass | STRING | No | No | Yes |  |  |  |
| RowStyle | STRING | No | No | Yes |  |  |  |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| Enabled | TControl | No | No | Yes |  | No |
| ReadOnly | TControl | No | No | Yes |  | No |
| Visible | TControl | No | No | Yes |  | No |

## TCheckbox
- Inheritance: TComponent > TControl > TFormControl > TCheckbox
- Parent must be: TContainer
- Description: TCheckbox

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| LabelAlign | STRING | Yes | No | No | right | left, right |  |
| TriState | BOOLEAN | Yes | No | Yes | false |  | Tri state checkbox |
| ValueChecked | STRING | Yes | No | Yes | Y |  | String representation of the value of checkbox checked, eg. Y |
| ValueNull | STRING | No | No | Yes |  |  | In case of tri-state mode the value represents the indeterminated state |
| ValueUnchecked | STRING | Yes | No | Yes | N |  | String representation of the value of checkbox unchecked, eg. N |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| Class | TControl | No | No | No |  | No |
| ControlSizeExtraSmall | TFormControl | No | No | No | @(TForm).ControlSizeExtraSmall | No |
| ControlSizeLarge | TFormControl | No | No | No | @(TForm).ControlSizeLarge | No |
| ControlSizeMedium | TFormControl | No | No | No | @(TForm).ControlSizeMedium | No |
| ControlSizeSmall | TFormControl | No | No | No | @(TForm).ControlSizeSmall | No |
| Enabled | TControl | Yes | No | Yes | true | No |
| FunctionCode | TComponent | No | No | No |  | No |
| HelpText | TControl | No | No | No |  | No |
| Label | TControl | No | No | No | @this.DBField.Label | No |
| LabelClass | TControl | No | No | No |  | No |
| LabelSizeExtraSmall | TFormControl | No | No | No | @(TForm).LabelSizeExtraSmall | No |
| LabelSizeLarge | TFormControl | No | No | No | @(TForm).LabelSizeLarge | No |
| LabelSizeMedium | TFormControl | No | No | No | @(TForm).LabelSizeMedium | No |
| LabelSizeSmall | TFormControl | No | No | No | @(TForm).LabelSizeSmall | No |
| PlaceHolder | TFormControl | No | No | No |  | No |
| ReadOnly | TControl | Yes | No | Yes | false | No |
| Required | TFormControl | No | Yes | Yes | false | No |
| Style | TControl | No | No | No |  | No |
| Visible | TControl | Yes | No | Yes | true | No |

### Events
| Name | Type | Description |
|---|---|---|
| onChecked | GUI |  |
| onIndeterminate | GUI | In case of tri-state mode it fires when user change its state to indeterminated |
| onUnchecked | GUI |  |

## TDateTimePicker
- Inheritance: TComponent > TControl > TFormControl > TDateTimePicker
- Parent must be: TContainer
- Description: TDateTimePicker

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| CalendarIcon | STRING | No | No | No |  |  | Icon displayed next to the input field of DateTimePicker |
| DisabledDaysOfWeek | STRING | No | No | No |  |  | Disables the section of days of the week, e.g. weekends. Default is [] and accepts an array of numbers from 0-6. |
| DisabledHours | STRING | No | No | No |  |  | ill allow or disallow hour selections but will affect all days |
| JSDateTimeFormat | STRING | Yes | No | Yes | @(parameter).JSDateTimeHMFormat |  |  |
| PosHorizontal | STRING | No | No | No |  | auto, left, right |  |
| PosVertical | STRING | No | No | No |  | auto, top, bottom |  |
| ShowCalendarWeeks | BOOLEAN | Yes | No | No | true |  | Show week number at the beginning of each line |
| ShowClear | BOOLEAN | Yes | No | No | true |  | Show the "Clear" button in the icon toolbar. Clicking the "Clear" button will set the calendar to null. |
| ShowClose | BOOLEAN | Yes | No | No | true |  | Show the "Close" button in the icon toolbar. |
| ShowOnFocus | BOOLEAN | Yes | No | No | true |  | Show DateTimePicker component when component receives focus. |
| ShowToday | BOOLEAN | Yes | No | No | true |  | Show the "Today" button in the icon toolbar. Clicking the "Today" button will set the calendar view and set the date to now. |
| SideBySide | BOOLEAN | Yes | No | No | true |  | When true date and time pickers are displayed on a single page, side-by-side. When false, one has to switch between date and time picker forms. |
| Stepping | NUMBER | No | No | No |  |  | Minute stepping in time picker, defaults to 1 |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| Class | TControl | No | No | No |  | No |
| ControlSizeExtraSmall | TFormControl | No | No | No | @(TForm).ControlSizeExtraSmall | No |
| ControlSizeLarge | TFormControl | No | No | No | @(TForm).ControlSizeLarge | No |
| ControlSizeMedium | TFormControl | No | No | No | @(TForm).ControlSizeMedium | No |
| ControlSizeSmall | TFormControl | No | No | No | @(TForm).ControlSizeSmall | No |
| Enabled | TControl | Yes | No | Yes | true | No |
| FunctionCode | TComponent | No | No | No |  | No |
| HelpText | TControl | No | No | No |  | No |
| Label | TControl | No | No | No | @this.DBField.Label | No |
| LabelClass | TControl | No | No | No |  | No |
| LabelSizeExtraSmall | TFormControl | No | No | No | @(TForm).LabelSizeExtraSmall | No |
| LabelSizeLarge | TFormControl | No | No | No | @(TForm).LabelSizeLarge | No |
| LabelSizeMedium | TFormControl | No | No | No | @(TForm).LabelSizeMedium | No |
| LabelSizeSmall | TFormControl | No | No | No | @(TForm).LabelSizeSmall | No |
| PlaceHolder | TFormControl | No | No | No |  | No |
| ReadOnly | TControl | Yes | No | Yes | false | No |
| Required | TFormControl | Yes | No | Yes | false | No |
| Style | TControl | No | No | No |  | No |
| Visible | TControl | Yes | No | Yes | true | No |

## TEdit
- Inheritance: TComponent > TControl > TFormControl > TEdit
- Parent must be: TContainer
- Description: TEdit

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| Align | STRING | Yes | No | No | left | left, center, right | Content alignment |
| AutoComplete | BOOLEAN | Yes | No | No | true |  | HTML AutoComplete on or off. Defaults to on. |
| HTMLInputType | STRING | No | No | No |  | text, password, email, hidden, number, button, checkbox, color, date , datetime , datetime-local , file, image, month , radio, range , reset, search, submit, tel, time , url, week | Standard HTML input type, defaults to text |
| Icon | STRING | No | No | No |  |  | Icon specified in the icon's library format, eg. "fa fa-share" or "glyphicon glyphicon-share" |
| IconAlign | STRING | No | No | No |  | left, right | Alignment of the icon. Left means before the component's label or content, right means after it. Defaults to left. |
| MaxLength | NUMBER | No | No | No | @this.DBField.Size |  | Maximum length of the field |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| Class | TControl | No | No | No |  | No |
| ControlSizeExtraSmall | TFormControl | No | No | No | @(TForm).ControlSizeExtraSmall | No |
| ControlSizeLarge | TFormControl | No | No | No | @(TForm).ControlSizeLarge | No |
| ControlSizeMedium | TFormControl | No | No | No | @(TForm).ControlSizeMedium | No |
| ControlSizeSmall | TFormControl | No | No | No | @(TForm).ControlSizeSmall | No |
| Enabled | TControl | Yes | No | Yes | true | No |
| FunctionCode | TComponent | No | No | No |  | No |
| HelpText | TControl | No | No | No |  | No |
| Label | TControl | No | No | No | @this.DBField.Label | No |
| LabelClass | TControl | No | No | No |  | No |
| LabelSizeExtraSmall | TFormControl | No | No | No | @(TForm).LabelSizeExtraSmall | No |
| LabelSizeLarge | TFormControl | No | No | No | @(TForm).LabelSizeLarge | No |
| LabelSizeMedium | TFormControl | No | No | No | @(TForm).LabelSizeMedium | No |
| LabelSizeSmall | TFormControl | No | No | No | @(TForm).LabelSizeSmall | No |
| PlaceHolder | TFormControl | No | No | No |  | No |
| ReadOnly | TControl | Yes | No | Yes | false | No |
| Required | TFormControl | Yes | No | Yes | false | No |
| Style | TControl | No | No | No |  | No |
| Visible | TControl | Yes | No | Yes | true | No |

### Events
| Name | Type | Description |
|---|---|---|
| onEnterKeyPressed | GUI | Fires when user presses Enter key in a TEdit component |

### Methods
| Name | Description |
|---|---|
| setFocus() | Sets focus on a form element |

## TFileUpload
- Inheritance: TComponent > TControl > TFormControl > TFileUpload
- Parent must be: TContainer
- Description: This is a comma separated list of mime types or extensions. E.g.: audio/*,video/*,image/png,.pdf

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| AcceptedFileTypes | STRING | Yes | No | No | image/* |  | This is a comma separated list of mime types or extensions. E.g.: audio/*,video/*,image/png,.pdf |
| FileProcessor | TFileProcessor | Yes | No | Yes |  |  |  |
| Icon | STRING | No | No | No |  |  | Icon specified in the icon's library format, eg. "fa fa-share" or "glyphicon glyphicon-share" |
| IconAlign | STRING | No | No | No |  | left, right | Alignment of the icon. Left means before the component's label or content, right means after it. Defaults to left. |
| MaxFiles | NUMBER | No | No | No |  |  |  |
| MaxFileSize | NUMBER | No | No | No |  |  | Maximum uploadable file size in MB. Defaults to 25MB. |
| ThumbnailHeight | NUMBER | No | No | No |  |  |  |
| ThumbnailWidth | NUMBER | No | No | No |  |  |  |
| UploadSender | STRING | No | Yes | Yes |  |  |  |
| UploadSenderData | STRING | No | Yes | Yes |  |  |  |
| UploadStatus | STRING | No | Yes | Yes |  |  |  |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| Class | TControl | No | No | No |  | No |
| ControlSizeExtraSmall | TFormControl | No | No | No | @(TForm).ControlSizeExtraSmall | No |
| ControlSizeLarge | TFormControl | No | No | No | @(TForm).ControlSizeLarge | No |
| ControlSizeMedium | TFormControl | No | No | No | @(TForm).ControlSizeMedium | No |
| ControlSizeSmall | TFormControl | No | No | No | @(TForm).ControlSizeSmall | No |
| Enabled | TControl | Yes | No | Yes | true | No |
| FunctionCode | TComponent | No | No | No |  | No |
| HelpText | TControl | No | No | No |  | No |
| Label | TControl | No | No | Yes | @this.DBField.Label | No |
| LabelClass | TControl | No | No | No |  | No |
| LabelSizeExtraSmall | TFormControl | No | No | No | @(TForm).LabelSizeExtraSmall | No |
| LabelSizeLarge | TFormControl | No | No | No | @(TForm).LabelSizeLarge | No |
| LabelSizeMedium | TFormControl | No | No | No | @(TForm).LabelSizeMedium | No |
| LabelSizeSmall | TFormControl | No | No | No | @(TForm).LabelSizeSmall | No |
| PlaceHolder | TFormControl | No | No | No | [:TFILEUPLOAD.PLACEHOLDER,Feltöltéshez kattintson vagy húzzon a szaggatott területre egy fájlt:] | No |
| ReadOnly | TControl | Yes | No | Yes | false | No |
| Required | TFormControl | Yes | No | Yes | false | No |
| Style | TControl | No | No | No |  | No |
| Visible | TControl | Yes | No | Yes | true | No |

### Events
| Name | Type | Description |
|---|---|---|
| onFileSelectionError | GUI | File cannot be added to the upload component due to some error, eg. file too big, too many files, etc. |
| onFileUploadError | GUI | Fires when an error occurs during file upload |

## THTMLEdit
- Inheritance: TComponent > TControl > TFormControl > THTMLEdit
- Parent must be: TContainer
- Description: THTMLEdit

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| HTMLEditorType | STRING | Yes | No | No | advanced | simple, advanced | Type of the HTML editor. Simple offers basic features, advanced offers more control over editing the text. |
| Icon | STRING | No | No | No |  |  | Icon specified in the icon's library format, eg. "fa fa-share" or "glyphicon glyphicon-share" |
| IconAlign | STRING | No | No | No |  | left, right | Alignment of the icon. Left means before the component's label or content, right means after it. Defaults to left. |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| Class | TControl | No | No | No |  | No |
| ControlSizeExtraSmall | TFormControl | No | No | No | @(TForm).ControlSizeExtraSmall | No |
| ControlSizeLarge | TFormControl | No | No | No | @(TForm).ControlSizeLarge | No |
| ControlSizeMedium | TFormControl | No | No | No | @(TForm).ControlSizeMedium | No |
| ControlSizeSmall | TFormControl | No | No | No | @(TForm).ControlSizeSmall | No |
| DBValue | TControl | No | Yes | No |  | No |
| Enabled | TControl | Yes | No | Yes | true | No |
| HelpText | TControl | No | No | No |  | No |
| Label | TControl | No | No | No | @this.DBField.Label | No |
| LabelClass | TControl | No | No | No |  | No |
| LabelSizeExtraSmall | TFormControl | No | No | No | @(TForm).LabelSizeExtraSmall | No |
| LabelSizeLarge | TFormControl | No | No | No | @(TForm).LabelSizeLarge | No |
| LabelSizeMedium | TFormControl | No | No | No | @(TForm).LabelSizeMedium | No |
| LabelSizeSmall | TFormControl | No | No | No | @(TForm).LabelSizeSmall | No |
| PlaceHolder | TFormControl | No | No | No |  | No |
| ReadOnly | TControl | Yes | No | Yes | false | No |
| Required | TFormControl | Yes | No | Yes | false | No |
| Style | TControl | No | No | No |  | No |
| Value | TControl | No | No | No |  | No |
| Visible | TControl | Yes | No | Yes | true | No |

## THTMLViewer
- Inheritance: TComponent > TControl > TFormControl > THTMLViewer
- Parent must be: TContainer
- Description: THTMLViewer

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| ScriptsAllowed | BOOLEAN | Yes | No | Yes | false |  | Allow scripts to be run in the viewer (sandboxed) |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| Class | TControl | No | No | No |  | No |
| ControlSizeExtraSmall | TFormControl | No | No | No | @(TForm).ControlSizeExtraSmall | No |
| ControlSizeLarge | TFormControl | No | No | No | @(TForm).ControlSizeLarge | No |
| ControlSizeMedium | TFormControl | No | No | No | @(TForm).ControlSizeMedium | No |
| ControlSizeSmall | TFormControl | No | No | No | @(TForm).ControlSizeSmall | No |
| Enabled | TControl | Yes | No | Yes | true | No |
| FunctionCode | TComponent | No | No | No |  | No |
| HelpText | TControl | No | No | No |  | No |
| Label | TControl | No | No | No | @this.DBField.Label | No |
| LabelClass | TControl | No | No | No |  | No |
| LabelSizeExtraSmall | TFormControl | No | No | No | @(TForm).LabelSizeExtraSmall | No |
| LabelSizeLarge | TFormControl | No | No | No | @(TForm).LabelSizeLarge | No |
| LabelSizeMedium | TFormControl | No | No | No | @(TForm).LabelSizeMedium | No |
| LabelSizeSmall | TFormControl | No | No | No | @(TForm).LabelSizeSmall | No |
| PlaceHolder | TFormControl | No | No | Yes |  | Yes |
| ReadOnly | TControl | No | No | Yes | true | Yes |
| Required | TFormControl | No | No | Yes |  | Yes |
| Style | TControl | No | No | No |  | No |
| Visible | TControl | Yes | No | Yes | true | No |

## TLabel
- Inheritance: TComponent > TControl > TFormControl > TLabel
- Parent must be: TComponent
- Description: TLabel

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| Icon | STRING | No | No | No |  |  | Icon specified in the icon's library format, eg. "fa fa-share" or "glyphicon glyphicon-share" |
| IconAlign | STRING | No | No | No |  | left, right | Alignment of the icon. Left means before the component's label or content, right means after it. Defaults to left. |
| Typography | STRING | Yes | No | No | p | p, span, h1, h2, h3, h4, h5, h6, blockquote, footer, var, samp | Text type eg. headings, paragraph, block quote, etc. |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| Class | TControl | No | No | No |  | No |
| ControlSizeExtraSmall | TFormControl | No | No | Yes |  | Yes |
| ControlSizeLarge | TFormControl | No | No | Yes |  | Yes |
| ControlSizeMedium | TFormControl | No | No | Yes |  | Yes |
| ControlSizeSmall | TFormControl | No | No | Yes |  | Yes |
| Enabled | TControl | Yes | No | Yes | true | No |
| FunctionCode | TComponent | No | No | No |  | No |
| HelpText | TControl | No | No | No |  | No |
| Label | TControl | No | No | No | @this.DBField.Label | No |
| LabelClass | TControl | No | No | No |  | No |
| LabelSizeExtraSmall | TFormControl | No | No | Yes |  | Yes |
| LabelSizeLarge | TFormControl | No | No | Yes |  | Yes |
| LabelSizeMedium | TFormControl | No | No | Yes |  | Yes |
| LabelSizeSmall | TFormControl | No | No | Yes |  | Yes |
| PlaceHolder | TFormControl | No | No | Yes |  | Yes |
| ReadOnly | TControl | No | No | Yes |  | Yes |
| Required | TFormControl | No | No | Yes |  | Yes |
| Style | TControl | No | No | No |  | No |
| Visible | TControl | Yes | No | Yes | true | No |

## TLOV
- Inheritance: TComponent > TControl > TFormControl > TLOV
- Parent must be: TContainer
- Description: TLOV

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| AJAXMode | BOOLEAN | Yes | No | Yes | false |  | Fetches associated list at render time (false) or loading time via AJAX (true) |
| AJAXQueueID | STRING | Yes | No | Yes | @this.Name |  |  |
| Diacritics | BOOLEAN | Yes | No | No | true |  | Pl. Aéró-t megtalálja aero-ra is |
| FieldId | STRING | Yes | No | Yes | id |  |  |
| FieldText | STRING | Yes | No | Yes | text |  |  |
| ForcedRefresh | BOOLEAN | Yes | No | Yes | false |  | Skip intelligent refresh check on GUI |
| Icon | STRING | No | No | No |  |  | Icon specified in the icon's library format, eg. "fa fa-share" or "glyphicon glyphicon-share" |
| IconAlign | STRING | No | No | No |  | left, right | Alignment of the icon. Left means before the component's label or content, right means after it. Defaults to left. |
| ListFilter | STRING | No | No | Yes |  |  | Source data filtering, URL format |
| ListSource | TQuery | Yes | No | Yes |  |  | JSON data provider that provides JSON-formatted data to the component |
| Master | TControl | No | No | Yes |  |  | Used in dependant control situations. Specifies the master component which this control depends on. When master component refreshes its value, this component will also be refreshed. |
| MasterFilterField | STRING | No | No | Yes |  |  |  |
| MaxSelLength | NUMBER | No | No | No |  |  | Only works with MultiSelect on. It limits the number of items can be selected in a dropdown list. Defaults to no limit. |
| MiddleClick | BOOLEAN | No | No | Yes |  |  | Enable middle click support on component's click event. |
| MinimumSearchLength | NUMBER | Yes | No | Yes | 1 |  |  |
| MultiSelect | BOOLEAN | Yes | No | Yes | false |  | When true, multiple entries can be selected in the control. When false, only a single entry is accepted. |
| Searchable | BOOLEAN | Yes | No | Yes | true |  | When true, search box is displayed on top of the dropdown. Defaults to true. |
| ServerSideSearch | BOOLEAN | Yes | No | Yes | false |  |  |
| ShowRefresh | BOOLEAN | Yes | No | No | false |  | Show Refresh button on component. Defaults to false. |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| Class | TControl | No | No | No |  | No |
| ControlSizeExtraSmall | TFormControl | No | No | No | @(TForm).ControlSizeExtraSmall | No |
| ControlSizeLarge | TFormControl | No | No | No | @(TForm).ControlSizeLarge | No |
| ControlSizeMedium | TFormControl | No | No | No | @(TForm).ControlSizeMedium | No |
| ControlSizeSmall | TFormControl | No | No | No | @(TForm).ControlSizeSmall | No |
| Enabled | TControl | Yes | No | Yes | true | No |
| FunctionCode | TComponent | No | No | No |  | No |
| HelpText | TControl | No | No | No |  | No |
| Label | TControl | No | No | No | @this.DBField.Label | No |
| LabelClass | TControl | No | No | No |  | No |
| LabelSizeExtraSmall | TFormControl | No | No | No | @(TForm).LabelSizeExtraSmall | No |
| LabelSizeLarge | TFormControl | No | No | No | @(TForm).LabelSizeLarge | No |
| LabelSizeMedium | TFormControl | No | No | No | @(TForm).LabelSizeMedium | No |
| LabelSizeSmall | TFormControl | No | No | No | @(TForm).LabelSizeSmall | No |
| Name | TComponent | No | No | Yes |  | No |
| PlaceHolder | TFormControl | No | No | No |  | No |
| ReadOnly | TControl | Yes | No | Yes | false | No |
| Required | TFormControl | Yes | No | Yes | false | No |
| Style | TControl | No | No | No |  | No |
| Visible | TControl | Yes | No | Yes | true | No |

### Events
| Name | Type | Description |
|---|---|---|
| onAfterRefresh | GUI | After control was refreshed successfully or unsuccessfully |

### Methods
| Name | Description |
|---|---|
| refresh() | Refreshes component data |

## TRadio
- Inheritance: TComponent > TControl > TFormControl > TRadio
- Parent must be: TContainer
- Description: TRadio

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| FieldId | STRING | Yes | No | Yes | id |  |  |
| FieldText | STRING | Yes | No | Yes | text |  |  |
| Icon | STRING | No | No | No |  |  | Icon specified in the icon's library format, eg. "fa fa-share" or "glyphicon glyphicon-share" |
| IconAlign | STRING | No | No | No |  | left, right | Alignment of the icon. Left means before the component's label or content, right means after it. Defaults to left. |
| ListFilter | STRING | No | No | Yes |  |  | Source data filtering, URL format |
| ListSource | TQuery | Yes | No | Yes |  |  | JSON data provider that provides JSON-formatted data to the component |
| RadioInline | BOOLEAN | Yes | No | No | false | true, false |  |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| Class | TControl | No | No | No |  | No |
| ControlSizeExtraSmall | TFormControl | No | No | No | @(TForm).ControlSizeExtraSmall | No |
| ControlSizeLarge | TFormControl | No | No | No | @(TForm).ControlSizeLarge | No |
| ControlSizeMedium | TFormControl | No | No | No | @(TForm).ControlSizeMedium | No |
| ControlSizeSmall | TFormControl | No | No | No | @(TForm).ControlSizeSmall | No |
| Enabled | TControl | Yes | No | Yes | true | No |
| FunctionCode | TComponent | No | No | No |  | No |
| HelpText | TControl | No | No | No |  | No |
| Label | TControl | No | No | No |  | No |
| LabelClass | TControl | No | No | No |  | No |
| LabelSizeExtraSmall | TFormControl | No | No | No | @(TForm).LabelSizeExtraSmall | No |
| LabelSizeLarge | TFormControl | No | No | No | @(TForm).LabelSizeLarge | No |
| LabelSizeMedium | TFormControl | No | No | No | @(TForm).LabelSizeMedium | No |
| LabelSizeSmall | TFormControl | No | No | No | @(TForm).LabelSizeSmall | No |
| PlaceHolder | TFormControl | No | No | Yes | false | Yes |
| ReadOnly | TControl | Yes | No | Yes | false | No |
| Required | TFormControl | Yes | No | Yes | true | No |
| Style | TControl | No | No | No |  | No |

## TStatic
- Inheritance: TComponent > TControl > TFormControl > TStatic
- Parent must be: TContainer
- Description: TStatic

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| Icon | STRING | No | No | No |  |  | Icon specified in the icon's library format, eg. "fa fa-share" or "glyphicon glyphicon-share" |
| IconAlign | STRING | No | No | No |  | left, right | Alignment of the icon. Left means before the component's label or content, right means after it. Defaults to left. |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| Class | TControl | No | No | No |  | No |
| ControlSizeExtraSmall | TFormControl | No | No | No | @(TForm).ControlSizeExtraSmall | No |
| ControlSizeLarge | TFormControl | No | No | No | @(TForm).ControlSizeLarge | No |
| ControlSizeMedium | TFormControl | No | No | No | @(TForm).ControlSizeMedium | No |
| ControlSizeSmall | TFormControl | No | No | No | @(TForm).ControlSizeSmall | No |
| Enabled | TControl | Yes | No | Yes | true | No |
| FunctionCode | TComponent | No | No | No |  | No |
| HelpText | TControl | No | No | No |  | No |
| Label | TControl | No | No | No | @this.DBField.Label | No |
| LabelClass | TControl | No | No | No |  | No |
| LabelSizeExtraSmall | TFormControl | No | No | No |  | No |
| LabelSizeLarge | TFormControl | No | No | No |  | No |
| LabelSizeMedium | TFormControl | No | No | No | @(TForm).LabelSizeMedium | No |
| LabelSizeSmall | TFormControl | No | No | No |  | No |
| PlaceHolder | TFormControl | No | No | Yes |  | Yes |
| ReadOnly | TControl | No | No | Yes |  | Yes |
| Required | TFormControl | No | No | Yes |  | Yes |
| Style | TControl | No | No | No |  | No |
| Visible | TControl | Yes | No | Yes | true | No |

## TText
- Inheritance: TComponent > TControl > TFormControl > TText
- Parent must be: TContainer
- Description: TText

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| Autosize | BOOLEAN | Yes | No | No | false |  | Autosize rows with animation as user in entering new rows |
| Cols | NUMBER | No | No | No |  |  |  |
| Icon | STRING | No | No | No |  |  | Icon specified in the icon's library format, eg. "fa fa-share" or "glyphicon glyphicon-share" |
| IconAlign | STRING | No | No | No |  | left, right | Alignment of the icon. Left means before the component's label or content, right means after it. Defaults to left. |
| MaxLength | NUMBER | No | No | No | @this.DBField.Size |  | Maximum length of the field |
| Rows | NUMBER | No | No | No |  |  | Number of rows to be displayed |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| Class | TControl | No | No | No |  | No |
| ControlSizeExtraSmall | TFormControl | No | No | No | @(TForm).ControlSizeExtraSmall | No |
| ControlSizeLarge | TFormControl | No | No | No | @(TForm).ControlSizeLarge | No |
| ControlSizeMedium | TFormControl | No | No | No | @(TForm).ControlSizeMedium | No |
| ControlSizeSmall | TFormControl | No | No | No | @(TForm).ControlSizeSmall | No |
| DBValue | TControl | No | Yes | No |  | No |
| Enabled | TControl | Yes | No | Yes | true | No |
| FunctionCode | TComponent | No | No | No |  | No |
| HelpText | TControl | No | No | No |  | No |
| Label | TControl | No | No | No | @this.DBField.Label | No |
| LabelClass | TControl | No | No | No |  | No |
| LabelSizeExtraSmall | TFormControl | No | No | No | @(TForm).LabelSizeExtraSmall | No |
| LabelSizeLarge | TFormControl | No | No | No | @(TForm).LabelSizeLarge | No |
| LabelSizeMedium | TFormControl | No | No | No | @(TForm).LabelSizeMedium | No |
| LabelSizeSmall | TFormControl | No | No | No | @(TForm).LabelSizeSmall | No |
| Name | TComponent | Yes | No | Yes | false | No |
| PlaceHolder | TFormControl | No | No | No |  | No |
| ReadOnly | TControl | Yes | No | Yes | false | No |
| Required | TFormControl | Yes | No | Yes | false | No |
| Style | TControl | No | No | No |  | No |
| Visible | TControl | Yes | No | Yes | true | No |

## TGrid
- Inheritance: TComponent > TControl > TGrid
- Parent must be: TContainer
- Description: TGrid

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| ActivePage | NUMBER | No | Yes | No | 1 |  |  |
| AJAXMode | BOOLEAN | Yes | No | Yes | true |  | Fetches associated list at render time (false) or loading time via AJAX (true) |
| AJAXQueueID | STRING | Yes | No | Yes | @this.Name |  |  |
| AutoFilterRefresh | BOOLEAN | Yes | No | Yes | true |  | Automatically refreshes the grid on filter change |
| AutoLoad | BOOLEAN | Yes | No | Yes | true |  | Loads data on render time. If false, manual refresh needed on client side. |
| CacheValidity | NUMBER | No | No | No |  |  | Cache validity in minutes |
| Caching | BOOLEAN | Yes | No | No | false |  | Disable or enable list source caching |
| ChartOptions | JSON | No | No | No | {} |  | ChartJS options |
| ChartXAxis | TDBField | No | No | No |  |  | X Axis of chart |
| ContainerClass | STRING | No | No | No |  |  |  |
| DataContainerClass | STRING | No | No | No |  |  |  |
| DataGenerated | BOOLEAN | No | Yes | Yes |  |  | Data is generated - in case of AJAX mode the value of this property is false to indicate to the JS side to refresh the grid's data |
| Exportable | BOOLEAN | Yes | No | No | true |  | This control can be exported eg. to Excel |
| ExportFunctionCode | STRING | No | No | No | GRID.EXPORT |  | Function code for exporting |
| FilterSQL | STRING | No | Yes | No |  |  | Generated SQL filter sentence |
| FilterTemplate | TEMPLATE | No | No | No |  |  | Hard-coded fixed filters to appear above the grid |
| GridHTMLType | STRING | Yes | No | Yes | table | table, div, div-responsive |  |
| LabelCacheInfo | STRING | Yes | No | Yes | [:GRID.CACHEINFO,Utoljára frissítve::] |  |  |
| LabelFilterButton | STRING | Yes | No | No | [:GRID.HEADER.FILTERS,Szűrők:] |  |  |
| LabelNoDataGenerated | STRING | Yes | No | No | [:GRID.HEADER.NO_DATA_GENERATED,Az adatok megjelenítéséhez frissíteni kell!:] |  | Label if no data generated to the grid (in case of first render when AutoLoad is false ) |
| LabelNoFilter | STRING | Yes | No | No | [:GRID.HEADER.NOFILTERS,Nincsenek szűrők beállítva:] |  |  |
| LabelRefresh | STRING | Yes | No | No | [:GRID.BUTTON.REFRESH,Frissítés:] |  |  |
| LabelSelectionOutOfList | STRING | Yes | No | No | [:GRID.HEADER.SELECTION,A kiválasztott tétel nem látszik:] |  |  |
| ListSource | TQuery | Yes | No | No |  |  | JSON data provider that provides JSON-formatted data to the component |
| ListsourceAlwaysReopen | BOOLEAN | Yes | No | Yes | false |  | In case multiple grids use a single listsource, reopen must be forced because of internal cache handling |
| LookupValue | STRING | No | No | Yes | @this.DBField.Value |  | Select row's value |
| Markable | BOOLEAN | Yes | No | Yes | false |  | Can act as a marker master |
| MarkerBatchID | STRING | Yes | No | Yes | @this.Name |  | Find the grids in the same batch |
| MarkerDBField | TDBField | No | No | No |  |  | Which field gives the row value in a client side marking scenario |
| MarkerStrategy | STRING | Yes | No | Yes | ExactMatch | ExactMatch, LastSmallerOrEqual, LastLargerOrEqual |  |
| MarkerValue | STRING | No | Yes | No |  |  |  |
| Master | TControl | No | No | Yes |  |  | Used in dependant control situations. Specifies the master component which this control depends on. When master component refreshes its value, this component will also be refreshed. |
| MasterDBField | TDBField | No | No | No |  |  |  |
| MasterValue | STRING | No | Yes | No |  |  |  |
| MultiSelect | BOOLEAN | Yes | No | Yes | false |  | When true, multiple entries can be selected in the control. When false, only a single entry is accepted. |
| MultiSelectDBField | TDBField | No | No | No |  |  | When grid is MultiSelect, this field contains the Value of the checkbox. If it is empty, checkbox doesn't shown - item is not checkable. |
| MultiSelectValue | STRING | No | Yes | No |  |  | MultiSelect checkbox value |
| PageCount | NUMBER | No | Yes | No |  |  |  |
| PersistencyPrefix | STRING | No | No | No |  |  |  |
| Persistent | STRING | No | No | Yes |  | DATABASE, SESSION |  |
| PublishFilterSQL | BOOLEAN | Yes | No | No | false |  | Publish filter SQL to page |
| RefreshWhenHidden | BOOLEAN | Yes | No | Yes | true |  | Refresh grid even if it is hidden |
| RowClass | STRING | No | No | No |  |  |  |
| RowCount | NUMBER | No | Yes | No |  |  |  |
| RowsPerPage | NUMBER | Yes | No | No | 0 |  | Maximum rows per page for pagination. Defaults to 10. |
| RowsPerPageList | STRING | Yes | No | No | 10,20,50,100,200 |  | Show these options in the pagination area |
| RowStyle | STRING | No | No | No |  |  |  |
| Scrollable | BOOLEAN | Yes | No | No | @(parameter).Tholos.TGrid.Scrollable\|false |  |  |
| ScrollableY | BOOLEAN | Yes | No | No | @(parameter).Tholos.TGrid.ScrollableY\|false |  |  |
| ScrollOnlyWhenFocused | BOOLEAN | Yes | No | No | false |  | Scroll only when focused (active) |
| ScrollToMarker | BOOLEAN | Yes | No | Yes | false |  | In case of marked rows change, scroll to the first one |
| Selectable | BOOLEAN | Yes | No | Yes | false |  |  |
| SelectedCount | NUMBER | No | Yes | Yes |  |  | Count of selected items when Multiselect is on |
| SelectedValues | TEXT | No | Yes | Yes |  |  | String of a JSON array with selected values |
| ShowCacheInfo | BOOLEAN | Yes | No | Yes | true |  |  |
| ShowExportButton | BOOLEAN | Yes | No | Yes | true |  | Show export button |
| ShowRefreshButton | BOOLEAN | Yes | No | Yes | true |  | Show or hide refresh button |
| ShowScrollCheckbox | BOOLEAN | Yes | No | Yes | true |  | Hide or show scroll checkbox |
| ShowTransposeCheckbox | BOOLEAN | Yes | No | Yes | true |  | Show transpose checkbox |
| SortedBy | TGridColumn | No | No | No |  |  |  |
| SortedByAlways | STRING | No | No | No |  |  | If set column headers sorting option is disabled and sends this to orderby |
| SortingDirection | STRING | No | Yes | No |  | ASC, DESC |  |
| Title | STRING | No | No | No |  |  |  |
| TotalRowCount | NUMBER | No | Yes | No |  |  |  |
| Transposed | BOOLEAN | Yes | No | Yes | false |  | Grid is transposed |
| UUID | STRING | No | Yes | Yes |  |  | Unique ID |
| ViewModeInit | STRING | Yes | No | Yes | GRID | GRID, CHART | Default view mode of grid |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| Class | TControl | No | No | No |  | No |
| Enabled | TControl | Yes | No | Yes | true | No |
| FunctionCode | TComponent | No | No | No |  | No |
| Label | TControl | No | No | Yes |  | Yes |
| LabelClass | TControl | No | No | Yes |  | Yes |
| ReadOnly | TControl | No | No | No |  | Yes |
| Style | TControl | No | No | No |  | No |
| Value | TControl | No | Yes | Yes |  | No |
| Visible | TControl | Yes | No | Yes | true | No |

### Events
| Name | Type | Description |
|---|---|---|
| onAfterRefresh | GUI | After control was refreshed successfully or unsuccessfully |
| onMasterValueChanged | GUI | Triggers when master value changed on a detail grid |
| onSelectionChange | GUI | Fired when selection changed in Multiselect mode |

### Methods
| Name | Description |
|---|---|
| clearSelection() | Clears selection |
| downloadExcel() | Download grid records in Excel format |
| getValues() | If component is multiselectable, getValues gives back the selected values in JSON array |
| initialize() | First time refresh. Runs only one time. |
| refresh() | Refreshes component data |
| setValueRefresh() | Sets value and refresh |
| setValues() | Set multiple values (checked items) to a Grid |
| setValuesRefresh() | Set selection values and refresh |
| showDetails() |  |
| updateMarkedRows() |  |

## THidden
- Inheritance: TComponent > TControl > THidden
- Parent must be: TContainer
- Description: THidden

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| Class | TControl | No | No | Yes |  | Yes |
| Enabled | TControl | No | No | Yes |  | Yes |
| FunctionCode | TComponent | No | No | No |  | No |
| HelpText | TControl | No | No | Yes |  | Yes |
| Label | TControl | No | No | Yes |  | Yes |
| LabelClass | TControl | No | No | Yes |  | Yes |
| ReadOnly | TControl | No | No | Yes |  | Yes |
| Style | TControl | No | No | Yes |  | Yes |
| Visible | TControl | No | No | Yes |  | Yes |

## TImage
- Inheritance: TComponent > TControl > TImage
- Parent must be: TContainer
- Description: TImage

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| AlternateText | STRING | No | No | No |  |  | Alternate text for the HTML image |
| ImageHeight | NUMBER | No | No | No |  |  | Height of the image |
| ImageWidth | NUMBER | No | No | No |  |  | Width of the image |
| Responsive | BOOLEAN | Yes | No | No | true |  | Images can be made responsive-friendly by using this flag. This applies max-width: 100%;, height: auto; and display: block; to the image so that it scales nicely to the parent element. |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| Class | TControl | No | No | No |  | No |
| Enabled | TControl | No | No | Yes | true | Yes |
| FunctionCode | TComponent | No | No | No |  | No |
| HelpText | TControl | No | No | No |  | No |
| Label | TControl | No | No | No | @this.DBField.Label | Yes |
| LabelClass | TControl | No | No | No |  | Yes |
| ReadOnly | TControl | No | No | Yes |  | Yes |
| Style | TControl | No | No | No |  | No |
| Visible | TControl | Yes | No | Yes | true | No |

## TLink
- Inheritance: TComponent > TControl > TLink
- Parent must be: TContainer
- Description: TLink

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| Action | TAction | No | No | Yes |  |  | Action that this component runs |
| ConfirmDialog | TConfirmDialog | No | No | Yes |  |  |  |
| Icon | STRING | No | No | No |  |  | Icon specified in the icon's library format, eg. "fa fa-share" or "glyphicon glyphicon-share" |
| IconAlign | STRING | No | No | No | left | left, right | Alignment of the icon. Left means before the component's label or content, right means after it. Defaults to left. |
| LinkTarget | STRING | No | No | No |  |  | href target tag |
| MiddleClick | BOOLEAN | Yes | No | No | true |  | Enable middle click support on component's click event. |
| URL | STRING | No | No | No |  |  | External URL of the resource |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| Class | TControl | No | No | No |  | No |
| Enabled | TControl | No | No | Yes | true | Yes |
| FunctionCode | TComponent | Yes | No | No |  | No |
| HelpText | TControl | No | No | No |  | No |
| Label | TControl | Yes | No | No |  | No |
| LabelClass | TControl | No | No | No |  | No |
| ReadOnly | TControl | No | No | Yes |  | Yes |
| Style | TControl | No | No | No |  | No |
| Visible | TControl | Yes | No | Yes | true | No |

### Methods
| Name | Description |
|---|---|
| navigate() | Navigate to this action |

## TButtonDropdownItem
- Inheritance: TComponent > TControl > TLink > TButtonDropdownItem
- Parent must be: TButtonDropdown
- Description: TDropDownItem

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| Enabled | TControl | No | No | Yes |  | Yes |

## TMenuItem
- Inheritance: TComponent > TControl > TLink > TMenuItem
- Parent must be: TComponent
- Description: TMenuItem

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| MenuType | STRING | Yes | No | No | submenuitem | menu, submenu, submenuitem |  |
| SubmenuHoverable | BOOLEAN | Yes | No | Yes | true |  |  |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| ConfirmDialog | TLink | No | No | Yes |  | Yes |
| Enabled | TControl | No | No | Yes |  | Yes |
| IconAlign | TLink | No | No | No | left | Yes |

## TMap
- Inheritance: TComponent > TControl > TMap
- Parent must be: TContainer
- Description: TMap

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| AutoFitZoom | BOOLEAN | Yes | No | Yes | false |  | Run fitZoom() after refresh |
| CenterLatitude | STRING | Yes | No | No |  |  |  |
| CenterLongitude | STRING | Yes | No | No |  |  |  |
| MapHeight | NUMBER | Yes | No | No |  |  | Height of map |
| MapTypeControl | BOOLEAN | Yes | No | No | false |  | Map type control enabled or disabled. It allows the user to switch between map types (map, satellite, terrain, etc.). Defaults to false. |
| MapWidth | STRING | Yes | No | No |  |  | Width of the map |
| MarkerClusterer | BOOLEAN | Yes | No | No | false |  |  |
| PanControl | BOOLEAN | Yes | No | No | true |  | Map pan control enabled or disabled. Defaults to false. |
| RefreshOnLoad | BOOLEAN | Yes | No | No | true |  | Run refresh() after load |
| ShowFitZoom | BOOLEAN | Yes | No | No | true | Show or hide fit zoom button on Google Maps |  |
| ShowLegend | BOOLEAN | Yes | No | Yes | true |  | Shows or hides legend that shows all available map sources. |
| ShowRefresh | BOOLEAN | Yes | No | No | true |  | Show Refresh button on component. Defaults to false. |
| StreetViewControl | BOOLEAN | Yes | No | No | false |  | Map street view feature enabled or disabled. Defaults to false. |
| ZoomControl | BOOLEAN | Yes | No | No | true |  | Map zoom controls are displayed or hidden. Defaults to true. |
| ZoomControlPosition | STRING | Yes | No | No | TOP_RIGHT | TOP_CENTER, TOP_LEFT, TOP_RIGHT, LEFT_TOP, RIGHT_TOP, LEFT_CENTER, RIGHT_CENTER, LEFT_BOTTOM, RIGHT_BOTTOM, BOTTOM_CENTER, BOTTOM_LEFT, BOTTOM_RIGHT | Defines the position of the map zoom control. Defaults to top left. |
| ZoomLevel | NUMBER | Yes | No | No | 5 |  | Controls the starting zoom level of the map as follows: 1: World 5: Landmass/continent 10: City 15: Streets 20: Buildings  Defaults to 5 |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| Class | TControl | No | No | No |  | No |
| DBField | TControl | No | No | Yes |  | Yes |
| DBParameterName | TControl | No | No | Yes |  | Yes |
| DBValue | TControl | No | No | Yes |  | Yes |
| Enabled | TControl | Yes | No | Yes | true | No |
| FunctionCode | TComponent | No | No | No |  | No |
| HelpText | TControl | No | No | No |  | No |
| Label | TControl | No | No | No |  | Yes |
| LabelClass | TControl | No | No | No |  | Yes |
| ReadOnly | TControl | Yes | No | Yes | false | No |
| Style | TControl | No | No | No |  | No |
| Visible | TControl | Yes | No | Yes | true | No |

### Events
| Name | Type | Description |
|---|---|---|
| onMarkerClick | GUI | Fires when a marker is clicked |

### Methods
| Name | Description |
|---|---|
| fitZoom() | Map will zoom to fit all visual components |
| refresh() | Refreshes component data |
| setCenter() |  |

## TDataProvider
- Inheritance: TComponent > TDataProvider
- Parent must be: only inherited types can be placed
- Description: TDataProvider

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| AutoOpenAllowed | BOOLEAN | No | Yes | No | true |  | AutoOpen component between construct and render time. Defaults to false. |
| AutoOpenAlwaysRequired | BOOLEAN | No | Yes | Yes | false |  | In Optimized mode it forces the application to automatically opens the component |
| CacheID | STRING | Yes | No | No | @this.Name |  | Cache ID |
| CacheInfo | ARRAY | No | Yes | No |  |  | Cache info |
| CacheMode | STRING | Yes | No | No | ReadWrite | ReadWrite, WriteOnly, ReadOnly | Cache access mode. In ReadWrite mode the component writes its result to the cache and reads it the next time it is opened. In WriteOnly mode the component always writes the cache, never reads it. In ReadOnly mode, the component always reads the cache, never writes. |
| CachePartitionedBy | STRING | No | No | No |  |  | The column name partitioning by. If empty the whole result will be cached, otherwise th result array will be cached in an indexed array with the first column. |
| CachePartitionFilter | TQueryFilter | No | No | No |  |  | Cache partition parameter. If exists and cache is partitioned only the referenced index will be refreshed. |
| CacheRefresh | BOOLEAN | No | Yes | No |  |  | Force cache to refresh |
| CacheSQLConflict | STRING | No | No | No |  | ReadCache, DisableCaching, RewriteCache | What to do if the SQL stored in cache is different from the actual one |
| CacheUsed | BOOLEAN | No | Yes | No |  |  | Cache is used for reading data |
| CacheValidity | NUMBER | No | No | No |  |  | Cache validity in minutes |
| Caching | STRING | Yes | No | No | Disabled | Disabled, Private, Shared | Caching methodology |
| DatabaseIndex | NUMBER | No | No | No |  |  | Database index in the config file to be used for database operations. It allows using multiple databases in an application. Defaults to 1. |
| DataProxy | TDataProxy | No | No | No |  |  | DataProxy component |
| PlainTextFormat | STRING | No | No | No | VALUE | KEY=VALUE, KEY:VALUE, KEY, VALUE | Plain text format |
| PlainTextRecordSeparator | STRING | No | No | No |  |  |  |
| ResponseType | STRING | Yes | No | No | JSON | HTML, JSON, JSONDATA, PLAINTEXT, XML, NONE | Type of the generated response. HTML will render a full HTML5-compliant page while HTMLSnippet will only render a partial useful for AJAX page generators. JSON and PLAINTEXT are self-explanatory. |
| XMLNamespace | STRING | No | No | No |  |  |  |
| XMLRootElement | STRING | No | No | No |  |  |  |
| XMLRowElement | STRING | No | No | No |  |  |  |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| NameSuffix | TComponent | No | No | Yes |  | No |

### Events
| Name | Type | Description |
|---|---|---|
| onBeforeOpen | PHP |  |
| onError | PHP |  |
| onSuccess | PHP |  |

## TExternalDataProvider
- Inheritance: TComponent > TDataProvider > TExternalDataProvider
- Parent must be: TRoute
- Description: TExternalDataProvider

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| curlDebug | BOOLEAN | No | No | Yes | @(parameter).Tholos.curl.debug\|false |  | Puts curl in debug mode (without verbose) |
| curlVerbose | BOOLEAN | No | No | Yes | @(parameter).Tholos.curl.verbose\|false |  | Puts cUrl calls to verbose mode |
| DataResultField | STRING | Yes | No | No | result |  |  |
| HTTPRequestHeader | TEXT | Yes | No | No | @(parameter).Tholos.HTTPRequestHeader |  |  |
| HTTPRequestMethod | STRING | Yes | No | No | POST | GET, HEAD, POST, PUT, DELETE, CONNECT, OPTIONS, TRACE, PATCH |  |
| JSONParameters | ARRAY | No | Yes | No |  |  |  |
| QueryLimit | NUMBER | No | Yes | No | 0 |  |  |
| QueryOffset | NUMBER | No | Yes | No | 0 |  |  |
| Result | TEXT | No | Yes | No |  |  |  |
| ResultType | STRING | No | Yes | No | ARRAY | ARRAY, JSON |  |
| RowCount | NUMBER | No | Yes | No |  |  |  |
| TimeOut | NUMBER | No | No | No | 0 |  |  |
| TotalRowCount | NUMBER | No | Yes | No |  |  |  |
| TotalRowCountField | STRING | No | No | No | totalRowCount |  |  |
| URL | STRING | Yes | No | No |  |  | External URL of the resource |
| URLPath | STRING | No | No | No |  |  |  |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| AutoOpenAllowed | TDataProvider | No | Yes | No | false | No |
| AutoOpenAlwaysRequired | TDataProvider | No | Yes | No | false | No |
| CacheID | TDataProvider | No | No | No | @this.Name | Yes |
| CacheInfo | TDataProvider | No | No | No |  | Yes |
| CacheMode | TDataProvider | No | No | No | ReadWrite | Yes |
| CachePartitionedBy | TDataProvider | No | No | Yes |  | Yes |
| CachePartitionFilter | TDataProvider | No | No | Yes |  | Yes |
| CacheRefresh | TDataProvider | No | No | No |  | Yes |
| CacheSQLConflict | TDataProvider | No | No | Yes |  | Yes |
| CacheUsed | TDataProvider | No | No | No |  | Yes |
| CacheValidity | TDataProvider | No | No | Yes |  | Yes |
| Caching | TDataProvider | No | No | No | Disabled | Yes |
| DatabaseIndex | TDataProvider | No | No | Yes |  | Yes |
| NameSuffix | TComponent | No | No | No |  | No |
| PlainTextFormat | TDataProvider | No | No | No | KEY=VALUE | No |
| PlainTextRecordSeparator | TDataProvider | No | No | No | , | No |

## TFileProcessor
- Inheritance: TComponent > TDataProvider > TFileProcessor
- Parent must be: TAction
- Description: TFileProcessor

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| ExtractArchive | BOOLEAN | Yes | No | No | false |  | Extract ZIP archive in case it uploaded |
| LocalFilePath | PARAMETER | Yes | No | No |  |  |  |
| MaximumFilesInArchive | NUMBER | Yes | No | No | 0 |  | The number of files archive can contain |
| Opened | BOOLEAN | No | Yes | No | false |  |  |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| AutoOpenAlwaysRequired | TDataProvider | No | Yes | No | true | No |
| CacheID | TDataProvider | No | No | Yes |  | Yes |
| CacheInfo | TDataProvider | No | No | Yes |  | Yes |
| CacheMode | TDataProvider | No | No | Yes |  | Yes |
| CachePartitionedBy | TDataProvider | No | No | Yes |  | Yes |
| CachePartitionFilter | TDataProvider | No | No | Yes |  | Yes |
| CacheRefresh | TDataProvider | No | No | Yes |  | Yes |
| CacheSQLConflict | TDataProvider | No | No | Yes |  | Yes |
| CacheUsed | TDataProvider | No | No | Yes |  | Yes |
| CacheValidity | TDataProvider | No | No | Yes |  | Yes |
| Caching | TDataProvider | No | No | Yes |  | Yes |
| DatabaseIndex | TDataProvider | No | No | Yes |  | Yes |
| DataProxy | TDataProvider | No | No | Yes |  | Yes |

## TJSONDataProvider
- Inheritance: TComponent > TDataProvider > TJSONDataProvider
- Parent must be: TDataProvider
- Description: TJSONDataProvider

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| DataResultField | STRING | Yes | No | Yes |  |  |  |
| DataResultMustBeIndexed | BOOLEAN | Yes | No | Yes | false |  | Data result is not an indexed array, it must be converted to an iterable array |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| AutoOpenAllowed | TDataProvider | No | Yes | Yes | false | No |
| CacheID | TDataProvider | No | No | Yes |  | Yes |
| CacheInfo | TDataProvider | No | No | Yes |  | Yes |
| CacheMode | TDataProvider | No | No | Yes |  | Yes |
| CachePartitionedBy | TDataProvider | No | No | Yes |  | Yes |
| CachePartitionFilter | TDataProvider | No | No | Yes |  | Yes |
| CacheRefresh | TDataProvider | No | No | Yes |  | Yes |
| CacheSQLConflict | TDataProvider | No | No | Yes |  | Yes |
| CacheUsed | TDataProvider | No | No | Yes |  | Yes |
| CacheValidity | TDataProvider | No | No | Yes |  | Yes |
| Caching | TDataProvider | No | No | Yes |  | Yes |
| DatabaseIndex | TDataProvider | No | No | Yes |  | Yes |
| DataProxy | TDataProvider | No | No | Yes |  | Yes |
| DevNote | TComponent | No | No | Yes |  | No |
| FunctionCode | TComponent | No | No | Yes |  | No |
| PlainTextFormat | TDataProvider | No | No | Yes |  | Yes |
| PlainTextRecordSeparator | TDataProvider | No | No | Yes |  | Yes |
| ResponseType | TDataProvider | No | No | Yes |  | No |
| XMLNamespace | TDataProvider | No | No | Yes |  | Yes |
| XMLRootElement | TDataProvider | No | No | Yes |  | Yes |
| XMLRowElement | TDataProvider | No | No | Yes |  | Yes |

## TQuery
- Inheritance: TComponent > TDataProvider > TQuery
- Parent must be: TRoute
- Description: TQuery

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| AuthProcedure | TStoredProcedure | No | No | No |  |  | Authentication procedure |
| CountTotalRows | BOOLEAN | No | Yes | No | false |  | If true TQuery will count the total number of result before generating result. TotalRowCount property will hold the value. It may differ from RowCount property which is the number of rows given back after QueryLimit applied. |
| DisableQueryFilters | BOOLEAN | No | Yes | No | false |  |  |
| DynamicMode | BOOLEAN | Yes | No | No | false |  | In dynamic mode TQuery object runs query two times: first query has to give back a new SQL command, which will be queried on the second run. |
| Filter | TEXT | No | No | No |  |  | Additional where clause |
| FilterArray | ARRAY | No | Yes | No |  |  |  |
| FilterError | BOOLEAN | No | Yes | Yes | false |  | Required filter missing, stop execution. |
| GlobalParameterPrefix | STRING | No | No | Yes |  |  | Adds all fields to the global parameter list with prefix |
| InitProcedure | TStoredProcedure | No | No | No |  |  | Initialization procedure - runs before query open |
| Opened | BOOLEAN | No | Yes | No | false |  |  |
| OrderBy | STRING | No | No | No |  |  |  |
| PlainTextHeader | BOOLEAN | Yes | No | No | false |  | Add columns as header to plain text output |
| PreparedSQL | TEXT | No | Yes | No |  |  |  |
| QueryLimit | NUMBER | No | Yes | No | 0 |  |  |
| QueryOffset | NUMBER | No | Yes | No | 0 |  |  |
| Result | TEXT | No | Yes | No |  |  |  |
| ResultType | STRING | No | Yes | No | ARRAY | ARRAY, JSON |  |
| RowCount | NUMBER | No | Yes | No |  |  |  |
| SQL | TEXT | Yes | No | No |  |  | SQL statement |
| StructureInfoOnly | BOOLEAN | No | Yes | No | false |  | It gives back (and caches) the query structure only *where 0=1". |
| TotalRowCount | NUMBER | No | Yes | No |  |  |  |
| TotalRowCountField | STRING | No | No | No |  |  |  |
| TotalRowCountSQL | STRING | No | No | No | @(parameter).TotalRowCountSQL |  |  |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| AutoOpenAlwaysRequired | TDataProvider | No | Yes | No | false | No |
| CacheSQLConflict | TDataProvider | Yes | No | No | DisableCaching | No |
| NameSuffix | TComponent | No | No | No |  | No |
| PlainTextFormat | TDataProvider | No | No | No | KEY=VALUE | No |
| PlainTextRecordSeparator | TDataProvider | No | No | No | , | No |
| XMLRootElement | TDataProvider | No | No | No | rows | No |
| XMLRowElement | TDataProvider | No | No | No | row | No |

### Events
| Name | Type | Description |
|---|---|---|
| onAuthError | PHP |  |
| onInitError | PHP |  |

## TQueryGroup
- Inheritance: TComponent > TDataProvider > TQueryGroup
- Parent must be: TRoute
- Description: TQueryGroup

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| AutoOpenAlwaysRequired | TDataProvider | No | Yes | No | false | No |
| CachePartitionedBy | TDataProvider | No | No | Yes |  | Yes |
| CachePartitionFilter | TDataProvider | No | No | Yes |  | Yes |
| CacheSQLConflict | TDataProvider | Yes | No | Yes | DisableCaching | No |
| DatabaseIndex | TDataProvider | No | No | Yes |  | Yes |
| FunctionCode | TComponent | No | No | Yes |  | No |
| PlainTextFormat | TDataProvider | No | No | Yes |  | Yes |
| PlainTextRecordSeparator | TDataProvider | No | No | Yes |  | Yes |
| ResponseType | TDataProvider | Yes | No | Yes | JSON | No |
| XMLNamespace | TDataProvider | No | No | Yes |  | Yes |
| XMLRootElement | TDataProvider | No | No | Yes |  | Yes |
| XMLRowElement | TDataProvider | No | No | Yes |  | Yes |

## TStoredProcedure
- Inheritance: TComponent > TDataProvider > TStoredProcedure
- Parent must be: TRoute
- Description: TStoredProcedure

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| CallbackParameter | TDBParam | No | No | No |  |  | Parameter contains the Tholos control structure, which will passed back to the GUI |
| CallbackResult | JSON | No | Yes | No |  |  | Runtime parameter, contains the Tholos control structure which will passed back to the GUI |
| ErrorCodeOnSuccess | NUMBER | No | No | No | 0 |  |  |
| ErrorCodeParameter | TDBParam | No | No | No |  |  |  |
| ErrorMessageParameter | TDBParam | No | No | No |  |  |  |
| GenerateDataResult | BOOLEAN | Yes | No | No | true |  | Give parameters back to the caller |
| LogDBParameterName | STRING | No | No | No | @this.LogParameter.Name |  |  |
| LoggerStoredProcedure | TStoredProcedure | No | No | No |  |  | Write back the received Log (PostgreSQL - Autonomous Transaction fix) |
| LogParameter | TDBParam | No | No | No |  |  | Log parameter (OUT) |
| ParameterNameTransformation | STRING | Yes | No | Yes | NONE | NONE, UPPERCASE, LOWERCASE, CAMELCASE, UNCAMELCASE |  |
| ParameterPrefix | STRING | No | No | No | P_ |  | Ha meg van adva, akkor ezzel a prefix-szel ellátott névvel keresi a TDBParam-okhoz az értékeket. Tehát, TDBParam=p_ID és a prefix p_, akkor az ID input a p_ID paraméterbe fog kerülni. |
| Procedure | STRING | Yes | No | No |  |  | Name of the SQL function |
| Result | TEXT | No | Yes | No |  |  |  |
| ResultErrorCode | NUMBER | No | Yes | No |  |  |  |
| ResultErrorMessage | STRING | No | Yes | No |  |  |  |
| ResultType | STRING | No | Yes | No | JSON | ARRAY, JSON |  |
| RollbackOnError | BOOLEAN | Yes | No | No | true |  | Rollback in case if Errorcode is not equal to ErrorCodeOnSuccess |
| Success | BOOLEAN | No | Yes | No | false |  | Execution success |
| ThrowException | BOOLEAN | Yes | No | No | false |  |  |
| TransactionMode | BOOLEAN | Yes | No | No | true |  | Use transaction |
| WriteErrorLogOnError | BOOLEAN | Yes | No | No | false |  | WriteErrorLog in case of handled error |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| AutoOpenAllowed | TDataProvider | No | Yes | No | false | No |
| AutoOpenAlwaysRequired | TDataProvider | No | Yes | No | false | No |
| CacheID | TDataProvider | No | No | Yes |  | Yes |
| CacheInfo | TDataProvider | No | No | Yes |  | Yes |
| CacheMode | TDataProvider | No | No | Yes |  | Yes |
| CachePartitionedBy | TDataProvider | No | No | Yes |  | Yes |
| CachePartitionFilter | TDataProvider | No | No | Yes |  | Yes |
| CacheRefresh | TDataProvider | No | No | Yes |  | Yes |
| CacheSQLConflict | TDataProvider | No | No | Yes |  | Yes |
| CacheUsed | TDataProvider | No | No | Yes |  | Yes |
| CacheValidity | TDataProvider | No | No | Yes |  | Yes |
| Caching | TDataProvider | No | No | Yes |  | Yes |
| NameSuffix | TComponent | No | No | No |  | No |
| PlainTextRecordSeparator | TDataProvider | No | No | Yes |  | Yes |

### Methods
| Name | Description |
|---|---|
| open() |  |

## TAPIPost
- Inheritance: TComponent > TDataProvider > TStoredProcedure > TAPIPost
- Parent must be: TRoute
- Description: TAPIPost

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| curlDebug | BOOLEAN | Yes | No | No | @(parameter).Tholos.curl.debug\|false |  | Puts curl in debug mode (without verbose) |
| curlVerbose | BOOLEAN | Yes | No | No | @(parameter).Tholos.curl.verbose\|false |  | Puts cUrl calls to verbose mode |
| ErrorCodeJSONField | STRING | No | No | No | errorCode |  |  |
| ErrorMessageJSONField | STRING | No | No | No | errorMsg |  |  |
| HTTPRequestHeader | TEXT | Yes | No | No | @(parameter).Tholos.HTTPRequestHeader |  |  |
| HTTPRequestMethod | STRING | Yes | No | No | POST | GET, HEAD, POST, PUT, DELETE, CONNECT, OPTIONS, TRACE, PATCH |  |
| HTTPResponseType | STRING | Yes | No | No | JSON |  | JSON HTML XML |
| SendAuthHeader | BOOLEAN | Yes | No | No | true |  | Send auth header according to TRoleManager.LoginIDHeader |
| TimeOut | NUMBER | No | No | Yes | 0 |  |  |
| URL | STRING | No | No | No | @(parameter).DevAPIURL\|@(parameter).DefaultAPIURL |  | External URL of the resource |
| URLPath | STRING | No | No | No |  |  |  |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| CallbackResult | TStoredProcedure | No | No | No |  | No |
| DatabaseIndex | TDataProvider | No | No | No |  | Yes |
| ErrorCodeOnSuccess | TStoredProcedure | No | No | No |  | No |
| LogDBParameterName | TStoredProcedure | No | No | No |  | No |
| ParameterNameTransformation | TStoredProcedure | Yes | No | No | NONE | No |
| ParameterPrefix | TStoredProcedure | No | No | No |  | No |
| PlainTextFormat | TDataProvider | No | No | No |  | No |
| PlainTextRecordSeparator | TDataProvider | No | No | No |  | Yes |
| Procedure | TStoredProcedure | No | No | No |  | Yes |
| ResultType | TStoredProcedure | No | Yes | No |  | No |
| RollbackOnError | TStoredProcedure | No | No | No |  | Yes |
| Success | TStoredProcedure | No | Yes | No |  | No |
| TransactionMode | TStoredProcedure | No | No | No |  | Yes |

## TDataProxy
- Inheritance: TComponent > TDataProxy
- Parent must be: TApplication
- Description: TDataProxy

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| AJAXMode | BOOLEAN | Yes | No | No | true |  | Fetches associated list at render time (false) or loading time via AJAX (true) |
| Enabled | BOOLEAN | Yes | No | No | true |  | When true (default), control is enabled. Otherwise control is disabled. |
| URL | STRING | Yes | No | No |  |  | External URL of the resource |

## TDBField
- Inheritance: TComponent > TDBField
- Parent must be: TDataProvider
- Description: TDBField

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| DataType | STRING | Yes | No | Yes |  | integer, string, text, bool, date, datetime, datetimehm, timestamp, time, float, JSON | Data type of the field |
| DateFormatParameter | STRING | No | No | Yes | @this.datatype | date, datetime, datetimehm, dateUniversal | DateFormatParameter will be prefixed with PHP\|JS\|DB to convert date\|datetime\|time data types from DBValue to Value and back. Date format is multilingual compatible. Lang parameter will be concatenated. |
| DBValue | STRING | No | Yes | Yes |  |  |  |
| FieldName | STRING | Yes | No | Yes |  |  | Field name in the database table |
| FormatString | STRING | No | No | Yes |  |  | Formatting value property |
| Index | NUMBER | Yes | No | Yes |  |  | Index of field in query |
| Label | STRING | Yes | No | Yes | @this.FieldName |  | Label text of the control |
| NativeDataType | STRING | Yes | No | Yes |  | OCI8.BFILE, OCI8.BLOB, OCI8.CFILE, OCI8.CHAR, OCI8.CLOB, OCI8.DATE, OCI8.DATETIME, OCI8.MLSLABEL, OCI8.NAMEDCOLLECTION, OCI8.NCHAR, OCI8.NCLOB, OCI8.NUMBER, OCI8.OBJECT, OCI8.OPAQUE, OCI8.RAW, OCI8.REF, OCI8.TABLE, OCI8.TIMESTAMP, OCI8.TIMESTAMP_DS, OCI8.TIMESTAMP_LTZ, OCI8.TIMESTAMP_TZ, OCI8.TIMESTAMP_YM, OCI8.VARCHAR, OCI8.VARCHAR2, OCI8.VARRAY, PGSQL.ANYARRAY, PGSQL.BOOL, PGSQL.BYTEA, PGSQL.CHAR, PGSQL.DATE, PGSQL.FLOAT4, PGSQL.FLOAT8, PGSQL.INET, PGSQL.INT2, PGSQL.INT2VECTOR, PGSQL.INT4, PGSQL.INT8, PGSQL.INTERVAL, PGSQL.NAME, PGSQL.NUMERIC, PGSQL.OID, PGSQL.OIDVECTOR, PGSQL.PG_DEPENDENCIES, PGSQL.PG_LSN, PGSQL.PG_MCV_LIST, PGSQL.PG_NDISTINCT, PGSQL.PG_NODE_TREE, PGSQL.REGPROC, PGSQL.REGTYPE, PGSQL.TEXT, PGSQL.TIMESTAMP, PGSQL.TIMESTAMPTZ, PGSQL.VARCHAR, PGSQL.XID, JSON.STRING, JSON.INTEGER, JSON.NUMBER, JSON.BOOL, JSON.TIMESTAMP, JSON.DATE, JSON.TIME, JSON.DATE-TIME |  |
| NullResultParameter | PARAMETER | No | No | Yes |  |  |  |
| ParseValue | BOOLEAN | Yes | No | Yes | false |  | Parse value as Tholos command sequence |
| Size | NUMBER | No | No | Yes |  |  |  |
| Value | STRING | No | Yes | Yes |  |  | Text value of the control |
| ValueChanged | BOOLEAN | No | Yes | Yes |  |  | Value changed |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| FunctionCode | TComponent | No | No | Yes |  | No |
| NameSuffix | TComponent | No | No | Yes |  | No |

### Events
| Name | Type | Description |
|---|---|---|
| onSetValue | PHP |  |

## TJSONField
- Inheritance: TComponent > TDBField > TJSONField
- Parent must be: TDBField
- Description: TJSONField

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| DataType | TDBField | Yes | No | Yes | string | No |
| Index | TDBField | No | No | Yes |  | Yes |
| NativeDataType | TDBField | No | No | Yes |  | Yes |
| Size | TDBField | No | No | Yes |  | Yes |
| ValueChanged | TDBField | No | No | Yes |  | No |

## TDBParam
- Inheritance: TComponent > TDBParam
- Parent must be: TDataProvider
- Description: TDBParam

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| AddToResult | BOOLEAN | Yes | No | Yes | true |  | Parameter will be given back to the caller |
| BindParameterName | STRING | Yes | No | Yes | @this.ParameterName |  |  |
| DataType | STRING | Yes | No | Yes |  | integer, string, text, bool, date, datetime, datetimehm, timestamp, time, float, JSON | Data type of the field |
| DateFormatParameter | STRING | No | No | Yes | @this.DataType | date, datetime, datetimehm, dateUniversal | DateFormatParameter will be prefixed with PHP\|JS\|DB to convert date\|datetime\|time data types from DBValue to Value and back. Date format is multilingual compatible. Lang parameter will be concatenated. |
| DefaultValue | STRING | No | No | Yes |  |  |  |
| MDB2DataType | STRING | No | No | Yes |  | text, boolean, integer, decimal, float, date, time, timestamp, clob, blob |  |
| NativeDataType | STRING | Yes | No | Yes |  | OCI8.BFILE, OCI8.BLOB, OCI8.CFILE, OCI8.CHAR, OCI8.CLOB, OCI8.DATE, OCI8.DATETIME, OCI8.MLSLABEL, OCI8.NAMEDCOLLECTION, OCI8.NCHAR, OCI8.NCLOB, OCI8.NUMBER, OCI8.OBJECT, OCI8.OPAQUE, OCI8.RAW, OCI8.REF, OCI8.TABLE, OCI8.TIMESTAMP, OCI8.TIMESTAMP_DS, OCI8.TIMESTAMP_LTZ, OCI8.TIMESTAMP_TZ, OCI8.TIMESTAMP_YM, OCI8.VARCHAR, OCI8.VARCHAR2, OCI8.VARRAY, PGSQL.ANYARRAY, PGSQL.BOOL, PGSQL.BYTEA, PGSQL.CHAR, PGSQL.DATE, PGSQL.FLOAT4, PGSQL.FLOAT8, PGSQL.INET, PGSQL.INT2, PGSQL.INT2VECTOR, PGSQL.INT4, PGSQL.INT8, PGSQL.INTERVAL, PGSQL.NAME, PGSQL.NUMERIC, PGSQL.OID, PGSQL.OIDVECTOR, PGSQL.PG_DEPENDENCIES, PGSQL.PG_LSN, PGSQL.PG_MCV_LIST, PGSQL.PG_NDISTINCT, PGSQL.PG_NODE_TREE, PGSQL.REGPROC, PGSQL.REGTYPE, PGSQL.TEXT, PGSQL.TIMESTAMP, PGSQL.TIMESTAMPTZ, PGSQL.VARCHAR, PGSQL.XID, JSON.STRING, JSON.INTEGER, JSON.NUMBER, JSON.BOOL, JSON.TIMESTAMP, JSON.DATE, JSON.TIME, JSON.DATE-TIME |  |
| ParameterMapping | JSON | No | No | Yes |  |  | In case of JSON type parameter, this property contains the parameter mapping options |
| ParameterMode | STRING | Yes | No | Yes | IN | IN, OUT, INOUT |  |
| ParameterName | STRING | Yes | No | Yes | @this.Name |  |  |
| ParseValue | BOOLEAN | Yes | No | Yes | false |  | Parse value as Tholos command sequence |
| SuppressDataTypeError | BOOLEAN | Yes | No | Yes | false |  | In case of inappropriate formatted input data the property controls whether an exception must be dropped or not. Use it carefully, it might hides programing errors! |
| Value | STRING | No | Yes | Yes |  |  | Text value of the control |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| FunctionCode | TComponent | No | No | Yes |  | No |
| NameSuffix | TComponent | No | No | Yes |  | No |

## TDiagramEditor
- Inheritance: TComponent > TDiagramEditor
- Parent must be: TContainer
- Description: TDiagramEditor

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| AllowLoopback | BOOLEAN | Yes | No | Yes | false |  | Allow loopback node connection |
| ControlHeight | STRING | Yes | No | Yes | 80vh |  | Height of the control |
| ControlWidth | STRING | Yes | No | Yes | 80vw |  | Width of the control |
| ListSourceConnectors | TQuery | Yes | No | Yes |  |  | List of connectors (source_id, target_id, type, [options]) |
| ListSourceNodes | TQuery | Yes | No | Yes |  |  | List of nodes (id, text, [options]) |
| Master | TControl | No | No | Yes |  |  | Used in dependant control situations. Specifies the master component which this control depends on. When master component refreshes its value, this component will also be refreshed. |
| MasterFilterField | STRING | No | No | Yes |  |  |  |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| FunctionCode | TComponent | No | No | Yes |  | No |
| Name | TComponent | No | No | Yes |  | No |
| NameSuffix | TComponent | No | No | Yes |  | No |

### Events
| Name | Type | Description |
|---|---|---|
| onConnect | GUI |  |
| onDisconnect | GUI |  |
| onMove | GUI |  |
| onSave | GUI |  |
| onSelect | GUI |  |

### Methods
| Name | Description |
|---|---|
| refresh() | Refreshes component data |

## TDocumentTitle
- Inheritance: TComponent > TDocumentTitle
- Parent must be: TComponent
- Description: TDocumentTitle

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| Tag | STRING | Yes | No | No | tholosDocumentTitle |  | HTML tag |
| Value | STRING | Yes | No | No |  |  | Text value of the control |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| FunctionCode | TComponent | No | No | Yes |  | Yes |
| NameSuffix | TComponent | No | No | Yes |  | No |

## TDPOpen
- Inheritance: TComponent > TDPOpen
- Parent must be: TComponent
- Description: TDPOpen

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| CloseDataSourceBeforeOpen | BOOLEAN | Yes | No | No | false |  |  |
| DataSource | TDataProvider | Yes | No | No |  |  |  |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| NameSuffix | TComponent | No | No | Yes |  | No |

## TGridControls
- Inheritance: TComponent > TGridControls
- Parent must be: only inherited types can be placed
- Description: TGridControls

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| DevNote | TComponent | No | No | Yes |  | No |
| FunctionCode | TComponent | No | No | Yes |  | No |
| NameSuffix | TComponent | No | No | Yes |  | No |

## TGridColumn
- Inheritance: TComponent > TGridControls > TGridColumn
- Parent must be: TComponent
- Description: TGridColumn

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| Align | STRING | Yes | No | No | left | left, center, right | Content alignment |
| ChartOptions | JSON | No | No | No | {} |  | ChartJS options |
| ChartType | STRING | No | No | No |  | bar, line, radar, polarArea, pie, doughnut | Chart available based on grid values |
| Class | STRING | No | No | No |  |  | HTML class. Used for injecting extra classes into the control. |
| ColumnOffset | NUMBER | Yes | No | No | 0 |  |  |
| ColumnSpan | NUMBER | No | No | No |  |  | ColumnSpan in table |
| DBField | TDBField | Yes | No | No |  |  | Database field attached to the control. When specified, the control is database-aware. |
| DBValue | STRING | No | Yes | No |  |  |  |
| Exportable | BOOLEAN | Yes | No | No | true |  | This control can be exported eg. to Excel |
| GridFilter | TGridFilter | No | No | No |  |  |  |
| HeadClass | STRING | No | No | No |  |  | Class of column's head |
| HeadStyle | STRING | No | No | No |  |  | Style of column's head |
| Label | STRING | Yes | No | No | @this.DBField.Label |  | Label text of the control |
| MarkChanges | BOOLEAN | Yes | No | No | false |  | Mark changes in the grid cell |
| Sortable | BOOLEAN | Yes | No | No | true |  | Column is sortable |
| Sorted | STRING | No | Yes | No |  | ASC, DESC |  |
| SortingDirection | STRING | Yes | No | No | ASC | ASC, DESC |  |
| Style | STRING | No | No | No |  |  | Style information |
| Template | TEMPLATE | No | No | No |  |  | Template for freestyle formatting :) |
| Value | STRING | No | Yes | No |  |  | Text value of the control |
| ValueChanged | BOOLEAN | No | Yes | No | false |  | Value changed |
| ValueChangedClass | STRING | No | No | No |  |  |  |
| ValueTemplate | TEMPLATE | No | No | No |  |  | Template for value formatting only |
| Visible | BOOLEAN | Yes | No | No | true |  | When true, control is visible, hidden otherwise |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| DevNote | TComponent | No | No | No |  | No |
| FunctionCode | TComponent | No | No | No |  | No |
| Name | TComponent | Yes | No | No |  | No |
| NameSuffix | TComponent | No | No | No |  | No |

## TGridFilter
- Inheritance: TComponent > TGridControls > TGridFilter
- Parent must be: TGrid
- Description: TGridFilter

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| CanBeNULL | BOOLEAN | Yes | No | Yes | true |  | Filter value can be NULL |
| DataType | STRING | Yes | No | Yes | @this.DBField.DataType | integer, string, text, bool, date, datetime, datetimehm, timestamp, time, float, JSON | Data type of the field |
| DBField | TDBField | Yes | No | Yes |  |  | Database field attached to the control. When specified, the control is database-aware. |
| DefaultRelation | STRING | No | No | Yes |  | eq, neq, gt, gteq, lt, lteq, NULL, NOT NULL, in, notin | Default relation |
| FieldId | STRING | No | No | Yes | id |  |  |
| FieldText | STRING | No | No | Yes | text |  |  |
| Label | STRING | Yes | No | Yes | @this.DBField.Label |  | Label text of the control |
| ListFilter | STRING | No | No | Yes |  |  | Source data filtering, URL format |
| ListSource | TQuery | No | No | Yes |  |  | JSON data provider that provides JSON-formatted data to the component |
| Value | STRING | No | No | Yes |  |  | Text value of the control |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| DevNote | TComponent | No | No | No |  | No |

## TGridParameter
- Inheritance: TComponent > TGridControls > TGridParameter
- Parent must be: TGrid
- Description: TGridParameter

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| DevNote | TComponent | No | No | No |  | No |

## TGridRow
- Inheritance: TComponent > TGridControls > TGridRow
- Parent must be: TGrid
- Description: TGridRow - handling multiple rows per record

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| Class | STRING | No | No | Yes |  |  | HTML class. Used for injecting extra classes into the control. |
| HideWhenEmpty | BOOLEAN | Yes | No | Yes | false |  |  |
| ShowColumnHead | BOOLEAN | Yes | No | Yes | false |  |  |
| Style | STRING | No | No | Yes |  |  | Style information |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| DevNote | TComponent | No | No | No |  | No |
| FunctionCode | TComponent | No | No | No |  | No |
| NameSuffix | TComponent | No | No | No |  | No |

## TIterator
- Inheritance: TComponent > TIterator
- Parent must be: TComponent
- Description: TIterator

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| DBField | TDBField | No | No | Yes |  |  | Database field attached to the control. When specified, the control is database-aware. |
| DBValue | STRING | No | Yes | Yes |  |  |  |
| JSONDBField | TDBField | No | No | Yes |  |  | Iterate through JSON datafield |
| JSONSource | JSON | No | No | Yes |  |  | Manual JSON source for iterate through |
| ListSource | TQuery | No | No | Yes |  |  | JSON data provider that provides JSON-formatted data to the component |
| RowCount | NUMBER | No | Yes | Yes |  |  |  |
| SourceURL | STRING | No | No | Yes |  |  | Source URL pointing to the requested resource |
| TotalRowCount | NUMBER | No | Yes | Yes |  |  |  |
| Value | STRING | No | No | Yes |  |  | Text value of the control |

### Events
| Name | Type | Description |
|---|---|---|
| onZeroResult | PHP |  |

## TJSLib
- Inheritance: TComponent > TJSLib
- Parent must be: TComponent
- Description: TJSLib

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| Placement | STRING | Yes | No | Yes | head | head, inplace, foot | Placement |
| URL | STRING | Yes | No | No |  |  | External URL of the resource |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| FunctionCode | TComponent | No | No | Yes |  | Yes |

## TLinkedComponent
- Inheritance: TComponent > TLinkedComponent
- Parent must be: TComponent
- Description: TLinkedComponent

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| Component | TComponent | Yes | No | No |  |  | Just a link to an other object |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| Name | TComponent | Yes | No | No |  | No |

## TMapSource
- Inheritance: TComponent > TMapSource
- Parent must be: TMap
- Description: TMapSource provides ListSource for the Google Maps-based TMap component

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| AJAXQueueID | STRING | Yes | No | Yes | @this.Name |  |  |
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
| Label | STRING | No | No | Yes |  |  | Label text of the control |
| ListSource | TQuery | Yes | No | Yes |  |  | JSON data provider that provides JSON-formatted data to the component |
| MapSourceType | STRING | Yes | No | Yes |  | Markers, Polylines, Polygons |  |
| Master | TControl | No | No | Yes |  |  | Used in dependant control situations. Specifies the master component which this control depends on. When master component refreshes its value, this component will also be refreshed. |
| MasterFilterField | STRING | No | No | Yes |  |  |  |
| ParentMap | STRING | No | Yes | Yes |  |  | TMap ID of the TMapSource object |
| Refreshing | BOOLEAN | No | Yes | Yes | false | TMapSource loading status |  |
| StrokeColor | STRING | No | No | Yes |  |  |  |
| StrokeOpacity | NUMBER | No | No | Yes |  |  |  |
| StrokeWeight | NUMBER | No | No | Yes |  |  |  |
| SwapAreaCoords | BOOLEAN | Yes | No | Yes | false |  |  |
| Visible | BOOLEAN | Yes | No | Yes | true |  | When true, control is visible, hidden otherwise |
| ZIndex | NUMBER | No | No | Yes |  |  |  |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| Name | TComponent | No | No | Yes |  | No |
| NameSuffix | TComponent | No | No | Yes |  | No |

### Methods
| Name | Description |
|---|---|
| refresh() | Refreshes component data |

## TPAdES
- Inheritance: TComponent > TPAdES
- Parent must be: TApplication
- Description: TPAdES

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| Enabled | BOOLEAN | Yes | No | No | true |  | When true (default), control is enabled. Otherwise control is disabled. |
| InputPDFFile | STRING | No | Yes | No |  |  | Input PDF file absolute path and filename |
| OutputPDFFile | STRING | No | Yes | No |  |  | Output PDF file absolute path and filename |
| Sender | TComponent | No | Yes | No |  |  | Sender object |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| FunctionCode | TComponent | No | No | No |  | Yes |
| Name | TComponent | Yes | No | No |  | No |
| NameSuffix | TComponent | No | No | No |  | Yes |

### Events
| Name | Type | Description |
|---|---|---|
| onError | PHP |  |
| onSignPDF | PHP |  |
| onSuccess | PHP |  |
| onValidatePDF | PHP |  |

## TParameter
- Inheritance: TComponent > TParameter
- Parent must be: only inherited types can be placed
- Description: TParameter

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| ParameterName | STRING | Yes | No | Yes |  |  |  |
| Value | STRING | No | No | Yes |  |  | Text value of the control |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| DevNote | TComponent | No | No | Yes |  | No |
| FunctionCode | TComponent | No | No | Yes |  | No |
| NameSuffix | TComponent | No | No | Yes |  | No |

## TAPIParameter
- Inheritance: TComponent > TParameter > TAPIParameter
- Parent must be: TExternalDataProvider
- Description: TAPIParameter

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| DevNote | TComponent | No | No | No |  | No |
| FunctionCode | TComponent | No | No | No |  | No |
| NameSuffix | TComponent | No | No | No |  | No |
| ParameterName | TParameter | Yes | No | No |  | No |
| Value | TParameter | No | No | No |  | No |

## TDataParameter
- Inheritance: TComponent > TParameter > TDataParameter
- Parent must be: TComponent
- Description: TDataParameter

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| DBField | TDBField | No | No | Yes |  |  | Database field attached to the control. When specified, the control is database-aware. |
| DBValue | STRING | No | Yes | Yes |  |  |  |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| DevNote | TComponent | No | No | No |  | No |
| ParameterName | TParameter | Yes | No | Yes | @this.DBField.FieldName\|@this.Name | No |

## TDataProxyParameter
- Inheritance: TComponent > TParameter > TDataProxyParameter
- Parent must be: TDataProxy
- Description: TDataProxyParameter

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| DevNote | TComponent | No | No | No |  | No |
| FunctionCode | TComponent | No | No | No |  | No |
| NameSuffix | TComponent | No | No | No |  | No |
| ParameterName | TParameter | Yes | No | No | @this.Name | No |
| Value | TParameter | No | No | No |  | No |

## TGlobalParameter
- Inheritance: TComponent > TParameter > TGlobalParameter
- Parent must be: TComponent
- Description: TGlobalParameter gets its value at render time and set it is global parameter

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| DevNote | TComponent | No | No | No |  | No |
| FunctionCode | TComponent | No | No | No |  | No |
| NameSuffix | TComponent | No | No | No |  | No |
| ParameterName | TParameter | Yes | No | No |  | No |
| Value | TParameter | No | No | No |  | No |

## TLOVParameter
- Inheritance: TComponent > TParameter > TLOVParameter
- Parent must be: TLOV
- Description: TLOVParameter

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| DevNote | TComponent | No | No | No |  | No |

## TPartial
- Inheritance: TComponent > TPartial
- Parent must be: TComponent
- Description: Rendered, result not given back but pushed into ParameterName

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| Cacheable | BOOLEAN | Yes | No | Yes | false |  | If not empty, generated content will be cached |
| ParameterName | STRING | Yes | No | Yes |  |  |  |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| FunctionCode | TComponent | No | No | Yes |  | No |
| NameSuffix | TComponent | No | No | Yes |  | No |

## TQueryFilter
- Inheritance: TComponent > TQueryFilter
- Parent must be: TComponent
- Description: TQueryFilter

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| DataType | STRING | Yes | No | Yes | text | integer, string, text, bool, date, datetime, datetimehm, timestamp, time, float, JSON | Data type of the field |
| DateFormatParameter | STRING | No | No | Yes | @this.datatype | date, datetime, datetimehm, dateUniversal | DateFormatParameter will be prefixed with PHP\|JS\|DB to convert date\|datetime\|time data types from DBValue to Value and back. Date format is multilingual compatible. Lang parameter will be concatenated. |
| DefaultValue | STRING | No | No | Yes |  |  |  |
| FieldName | STRING | No | No | Yes |  |  | Field name in the database table |
| FilterGroupParameter | STRING | No | No | No | :filter |  | Filter string in SQL query, defaults to :filter |
| NativeDataType | STRING | No | No | Yes |  | OCI8.BFILE, OCI8.BLOB, OCI8.CFILE, OCI8.CHAR, OCI8.CLOB, OCI8.DATE, OCI8.DATETIME, OCI8.MLSLABEL, OCI8.NAMEDCOLLECTION, OCI8.NCHAR, OCI8.NCLOB, OCI8.NUMBER, OCI8.OBJECT, OCI8.OPAQUE, OCI8.RAW, OCI8.REF, OCI8.TABLE, OCI8.TIMESTAMP, OCI8.TIMESTAMP_DS, OCI8.TIMESTAMP_LTZ, OCI8.TIMESTAMP_TZ, OCI8.TIMESTAMP_YM, OCI8.VARCHAR, OCI8.VARCHAR2, OCI8.VARRAY, PGSQL.ANYARRAY, PGSQL.BOOL, PGSQL.BYTEA, PGSQL.CHAR, PGSQL.DATE, PGSQL.FLOAT4, PGSQL.FLOAT8, PGSQL.INET, PGSQL.INT2, PGSQL.INT2VECTOR, PGSQL.INT4, PGSQL.INT8, PGSQL.INTERVAL, PGSQL.NAME, PGSQL.NUMERIC, PGSQL.OID, PGSQL.OIDVECTOR, PGSQL.PG_DEPENDENCIES, PGSQL.PG_LSN, PGSQL.PG_MCV_LIST, PGSQL.PG_NDISTINCT, PGSQL.PG_NODE_TREE, PGSQL.REGPROC, PGSQL.REGTYPE, PGSQL.TEXT, PGSQL.TIMESTAMP, PGSQL.TIMESTAMPTZ, PGSQL.VARCHAR, PGSQL.XID, JSON.STRING, JSON.INTEGER, JSON.NUMBER, JSON.BOOL, JSON.TIMESTAMP, JSON.DATE, JSON.TIME, JSON.DATE-TIME |  |
| ParameterName | STRING | No | No | Yes |  |  |  |
| RecordSelector | STRING | No | No | Yes | false | false, true | This filter is used to select a specific record in the query. RecordSelector filters applied only on AutoOpened query-s and usually has default value. |
| Relation | STRING | Yes | No | Yes | = |  |  |
| Required | BOOLEAN | Yes | No | Yes | false |  | Control is a required input |
| SQL | TEXT | No | Yes | Yes |  |  | SQL statement |
| Value | STRING | No | No | Yes |  |  | Text value of the control |
| ValueList | BOOLEAN | Yes | No | Yes | false |  | Value is a comma separated list |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| NameSuffix | TComponent | No | No | Yes |  | No |

## TQueryFilterGroup
- Inheritance: TComponent > TQueryFilterGroup
- Parent must be: TComponent
- Description: TQueryFilterGroup

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| EmptySQL | TEXT | No | No | Yes |  |  |  |
| FilterGroupParameter | STRING | Yes | No | Yes | :filter |  | Filter string in SQL query, defaults to :filter |
| InternalOperator | STRING | Yes | No | Yes |  | AND, OR, AND NOT, OR NOT |  |
| SQL | TEXT | No | Yes | Yes |  |  | SQL statement |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| FunctionCode | TComponent | No | No | Yes |  | No |
| NameSuffix | TComponent | No | No | Yes |  | No |

## TRoleManager
- Inheritance: TComponent > TRoleManager
- Parent must be: TApplication
- Description: TRoleManager

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| AccessDeniedURL | STRING | No | No | No |  |  | In case of access denied, redirect to URL |
| AuthorizationHeader | STRING | No | No | No | @(parameter).Tholos.AuthorizationHeader |  | Name of authorization header (case sensitive) |
| CSRFCookieName | STRING | No | No | No | @(parameter).Tholos.CSRFCookieName |  | CSRF Cookie name. If its set then CSRF token generation is disabled |
| CSRFEnabled | BOOLEAN | Yes | No | No | @(parameter).Tholos.CSRFEnabled\|false |  | Cross Site Request Forgery handling |
| CSRFHeaderName | STRING | Yes | No | No | @(parameter).Tholos.CSRFHeaderName\|anti-csrf-token |  |  |
| DisableInitSessionProvider | BOOLEAN | Yes | No | No | false |  | Disable InitSessionProvider in application init phase (in case of proxy is enabled this should be disabled because of the behavior of separate remote calls) |
| Enabled | BOOLEAN | Yes | No | No | @(parameter).Tholos.EnableRoleManager\|true |  | When true (default), control is enabled. Otherwise control is disabled. |
| ErrorMessage | STRING | Yes | No | No |  |  |  |
| ExtraHeaders | STRING | No | No | No |  |  | List of headers which will be included into every HTTP Request, separated by comma |
| FunctionCodes | ARRAY | No | Yes | No |  |  |  |
| FunctionCodesHeader | STRING | No | No | No | @(parameter).Tholos.FunctionCodesHeader |  | Name of header contains function list |
| FunctionCodesHeaderType | STRING | No | No | No | CSV | JSON, CSV | Type of list of function codes in header |
| InitSessionProvider | TStoredProcedure | No | No | No |  |  |  |
| ListSource | TQuery | No | No | No |  |  | JSON data provider that provides JSON-formatted data to the component |
| LoginID | STRING | No | Yes | No |  |  |  |
| LoginIDHeader | STRING | No | No | No |  |  | Send Rolemanager.loginID in HTTP header with this name |
| LoginURL | STRING | No | No | No |  |  | Redirect URL in case of no user logged in and component has defined function code |
| LogoutURL | STRING | No | No | No |  |  | In case of logout user will be redirected to this URL |
| NewLoginHeader | STRING | No | No | No | @(parameter).Tholos.NewLoginHeader |  | Name of header signal new login happened |
| SessionExpiredURL | STRING | No | No | No |  |  | PHP Session expired, where to redirect user |
| UsernameHeader | STRING | No | No | No | @(parameter).Tholos.UsernameHeader |  | Name of header contains user name |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| FunctionCode | TComponent | No | No | Yes |  | Yes |
| Name | TComponent | No | No | No |  | No |

## TRoute
- Inheritance: TComponent > TRoute
- Parent must be: TApplication
- Description: TRoute

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| DefaultAction | TAction | No | No | Yes |  |  | Default action of the route when not specified |
| InitSessionProvider | TStoredProcedure | No | No | Yes |  |  |  |
| PersistentSession | BOOLEAN | Yes | No | No | true |  | If it's false, PHP destroys session on finish |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| FunctionCode | TComponent | Yes | No | No | # | No |
| NameSuffix | TComponent | No | No | Yes |  | No |

## TTemplate
- Inheritance: TComponent > TTemplate
- Parent must be: TAction
- Description: TTemplate

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| Template | TEMPLATE | Yes | No | Yes |  |  | Template for freestyle formatting :) |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| NameSuffix | TComponent | No | No | Yes |  | No |

## TTimer
- Inheritance: TComponent > TTimer
- Parent must be: TComponent
- Description: TTimer

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| Async | BOOLEAN | Yes | No | No | true |  | Asynchronus call. If false, it does not execute the onExecute trigger and waits RetryInterval msec before the next execution. |
| AutoStart | BOOLEAN | Yes | No | Yes | true |  | Start timer on document ready, otherwise Execute method must called manually. |
| Enabled | BOOLEAN | Yes | No | Yes | true |  | When true (default), control is enabled. Otherwise control is disabled. |
| EnabledOnActivate | BOOLEAN | Yes | No | Yes | false |  | If true then activate() method overwrites enabled property to true. In other case enabled must be turned on manually before activates run. |
| Interval | NUMBER | Yes | No | No | 5000 |  | Interval in milliseconds |
| RetryInterval | NUMBER | Yes | No | No | 100 |  | RetryInterval in milliseconds in synchronous mode. |
| StartInterval | NUMBER | Yes | No | No | @this.Interval |  | First run |
| TimerMode | STRING | Yes | No | Yes | interval | interval, timeout | interval: run in every Interval msec timeout: run once after Interval msec |
| WaitOnInactiveTab | BOOLEAN | Yes | No | Yes | true |  |  |

### Events
| Name | Type | Description |
|---|---|---|
| onExecute | GUI |  |

### Methods
| Name | Description |
|---|---|
| activate() |  |
| execute() |  |
| setEnabled() | Set component enabled/disabled |

## TWorkflow
- Inheritance: TComponent > TWorkflow
- Parent must be: TComponent
- Description: TWorkflow

### Properties
| Name | Type | Mandatory | Runtime | Hidden data | Default | Values | Description |
|---|---|---|---|---|---|---|---|
| Parallel | BOOLEAN | Yes | No | Yes | false |  | Execute all belonging TWorkFlowStep parallel |

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| NameSuffix | TComponent | No | No | Yes |  | No |

### Methods
| Name | Description |
|---|---|
| execute() |  |

## TWorkflowStep
- Inheritance: TComponent > TWorkflowStep
- Parent must be: TWorkflow
- Description: TWorkflowStep

### Inherited property overrides
Inherited properties whose settings differ from the parent type.

| Name | Defined in | Mandatory | Runtime | Hidden data | Default | Disabled |
|---|---|---|---|---|---|---|
| NameSuffix | TComponent | No | No | Yes |  | No |

### Events
| Name | Type | Description |
|---|---|---|
| onError | GUI |  |
| onExecute | GUI |  |
| onFinished | GUI | onFinished triggered when onExecute does not return a success or error event (eg: setVisible) |
| onResume | GUI | onResume triggered when async execution completed |
| onSuccess | GUI |  |
| onWait | GUI | onWait triggered on async step execution |

### Methods
| Name | Description |
|---|---|
| execute() |  |

