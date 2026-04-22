# Tholos Framework

Tholos is a PHP component-based GUI framework built on top of the [Eisodos](https://github.com/offsite-solutions/eisodos) framework. It provides a server-rendered, database-driven component architecture for building HTML applications with AJAX capabilities, PDF generation, role-based access control, and data caching.

Applications are designed visually using **TholosBuilder** (a companion web app) and compiled into `.tcd` (Tholos Component Definition) cache files that the Tholos runtime loads and renders.

## Requirements

- PHP >= 8.4
- Eisodos framework (`offsite-solutions/eisodos`)
- Database: Oracle or PostgreSQL (via Eisodos connectors)
- Redis (optional, for caching)
- Extensions: json, mbstring, bcmath, pcntl, simplexml, zip, openssl, curl, redis

## Installation

```bash
# Production
composer install --no-interaction --prefer-dist --no-dev --no-ansi --optimize-autoloader

# Development (symlinks Eisodos from ../../_eisodos/Base)
COMPOSER=composer.dev.json composer update --no-interaction --prefer-dist --no-dev --no-ansi --optimize-autoloader
```

---

## Architecture Overview

### Bootstrap Chain

```
Tholos::init()
  +-- TholosLogger::init()
  +-- TholosApplication::init()
        +-- Load _tholos.init (component types, type index, component index)
        +-- Load .tcd files (component definitions for current route)
        +-- Instantiate TApplication (mandatory root)
        +-- Instantiate TRoleManager (optional)
        +-- Resolve TRoute from tholos_route parameter
        +-- Resolve TAction from tholos_action parameter (default: 'index')
        +-- Instantiate all components in creation order
        +-- initComponents() -- calls init() on every component
        +-- autoOpen() -- runs TDataProviders with AutoOpenAllowed=true
        +-- render() -- walks the component tree, returns HTML
        +-- Finalize response (HTML, JSON, PDF, XML, etc.)
```

Access singletons via `Tholos::$app` (TholosApplication) and `Tholos::$logger` (TholosLogger).

### Application Structure

Every Tholos application follows this component hierarchy:

```
TApplication
  +-- TRoleManager (optional, access control)
  +-- TPage / TPDFPage (layout templates)
  +-- TDataProxy (optional, remote server calls)
  +-- TPAdES (optional, digital signatures)
  +-- TRoute (one per logical route)
        +-- TAction (one or more per route)
        |     +-- TTemplate / TContainer / TForm / TModal / ...
        |     +-- TConfirmDialog
        |     +-- TFileProcessor
        +-- TStoredProcedure / TQuery / TQueryGroup
        +-- TExternalDataProvider / TAPIPost
```

### Request Flow

1. **Route Resolution** -- `tholos_route` parameter selects the `TRoute` component
2. **Action Resolution** -- `tholos_action` parameter selects the `TAction` (default: `index`)
3. **Initialization** -- all components' `init()` is called; TRoute optionally runs `InitSessionProvider`
4. **Auto-Open** -- TDataProviders with `AutoOpenAllowed=true` execute their queries/procedures
5. **Rendering** -- recursive tree walk: each component's `render()` returns HTML; children are rendered first, their output passed as `$content` to the parent
6. **Response** -- output finalized based on TAction's `ResponseType` property

---

## Component System

All components extend `TComponent`. Components are defined in TholosBuilder and compiled into `.tcd` files. At runtime, TholosApplication loads these definitions, instantiates the PHP classes, and calls their lifecycle methods.

### Component Lifecycle

```
Constructor (properties and events loaded from .tcd)
  |
  v
init()
  +-- Set runtime properties
  +-- Fire onAfterInit event
  |
  v
render(sender, content)
  +-- checkRole() -- verify FunctionCode access
  +-- Fire onBeforeRender event
  +-- generateProps() -- populate prop_* template parameters
  +-- generateEvents() -- populate event_* parameters, fire PHP events
  +-- Eisodos template engine renders the component template
  +-- Fire onAfterRender event
  +-- Return HTML string
```

### Properties

Each component has typed properties defined in TholosBuilder:

| Type | Description |
|------|-------------|
| `STRING` | Plain string value |
| `NUMBER` | Numeric value |
| `BOOLEAN` | true/false (configurable via `Tholos.BoolTrue` / `Tholos.BoolFalse`) |
| `TEXT` | Multi-line text |
| `JSON` | JSON structure |
| `ARRAY` | Array value |
| `TEMPLATE` | Eisodos template name |
| `PARAMETER` | Reference to an Eisodos parameter (resolved via `$parameterHandler->getParam()`) |
| `COMPONENT` | Reference to another component by ID |

Properties are accessed via `$component->getProperty('Name', $default)` which automatically parses dynamic expressions.

### Property Expression Language

Property values support a dynamic expression syntax that is resolved at runtime:

**Reference expressions (`@`)**

| Syntax | Resolves to |
|--------|-------------|
| `@this.PropertyName` | This component's own property |
| `@parent.PropertyName` | Parent component's property |
| `@route.PropertyName` | Current route's property |
| `@ComponentName.PropertyName` | Named sibling component's property |
| `@(ComponentType).PropertyName` | First parent of the given type |
| `@(>ComponentType).PropertyName` | First child of the given type |
| `@(parameter).ParamName` | Eisodos parameter value |

**Defaults (`|`)** -- cascading fallback chain:
```
@(parameter).Tholos.TGrid.Scrollable|false
```
If the parameter doesn't exist, the value `false` is used. Multiple defaults can be chained: `@comp.prop|@other.prop|literal`.

**Concatenation (`++`)** -- joins multiple expressions:
```
@this.Name++_++@(parameter).Suffix
```

**Chained property access (`.`)** -- navigate through COMPONENT-type properties:
```
@MyDataProvider.DBField.Value
```
If `DBField` is a COMPONENT property, it resolves to that component, then reads `Value` from it.

**Callback (`%`)** -- calls a template callback function:
```
%callback_function_name
```

### Events

Components define two types of events:

**PHP Events** (`type=PHP`) -- server-side, called during rendering:
- Referenced as `Application\EventClass::methodName`
- Called via `Tholos::$app->eventHandler($component, 'eventName')`
- The handler receives the sender component and can modify properties/response
- Common: `onBeforeRender`, `onAfterRender`, `onAfterInit`, `onBeforeCreate`

**GUI Events** (`type=GUI`) -- client-side JavaScript:
- Value is a JavaScript function call or component method reference
- Rendered into the HTML template as `event_eventname` parameters
- Client-side dispatch: `Tholos.eventHandler(senderID, targetID, componentType, methodName, routePath, eventData, userParameters)`

### Role-Based Access Control

`TRoleManager` (optional child of TApplication) controls access:
- Each component can set a `FunctionCode` property
- At render time, `checkRole()` verifies the current user has that function code
- Use `#` as FunctionCode to require login without a specific role
- CSRF protection: enabled via `CSRFEnabled`, token in `csrf_token_value` parameter, header name in `csrf_header_name`

---

## Component Reference

For the full, detailed component type reference (all properties, events, methods, inheritance, and parent constraints), see:

**[docs/Tholos_Component_Types.md](docs/Tholos_Component_Types.md)**

Below is a functional overview of each component category.

### Layout & Structure

| Component | Description |
|-----------|-------------|
| `TAction` | Defines an action within a route. `ResponseType` controls output format: `HTML`, `JSON`, `JSONDATA`, `PLAINTEXT`, `XML`, `PDF`, `NONE`. References a `TPage` for layout. |
| `TPage` | HTML page layout wrapper. Renders its `Template` property via Eisodos template engine. |
| `TTemplate` | Renders an Eisodos template by name (from `Template` property). |
| `TContainer` | Generic HTML container. `ContainerType` property sets the tag (`div`, `span`, `section`, etc.). |
| `TCell` | Table cell container. |
| `TColumn` | Column-based layout container. |
| `TFormContainer` | Groups form controls visually. |
| `TWidget` | Panel/card container with optional header. |
| `TModal` | Bootstrap modal dialog. |
| `TPartial` | Renders content and stores it in a named parameter (`ParameterName`) instead of outputting HTML. Supports `Cacheable` mode. |
| `TDocumentTitle` | Sets the HTML `<title>`. |
| `TJSLib` | Includes JavaScript/CSS files via head or foot items. |

### Navigation & Routing

| Component | Description |
|-----------|-------------|
| `TRoute` | Represents a route. Matched by `tholos_route` parameter. Can run `InitSessionProvider` on init. `PersistentSession` controls session persistence. |
| `TAction` | Action within a route. Matched by `tholos_action` parameter (default: `index`). `HTTPMethod` constrains allowed methods (GET, POST, etc.). |
| `TConfirmDialog` | Confirmation dialog with configurable buttons (OK, Cancel, Yes, No, Custom). |
| `TConfirmButton` | Button within a TConfirmDialog. |

### Form Controls

All form controls extend `TFormControl` which extends `TControl`. Common properties: `DBField` (data binding), `Value`, `Label`, `LabelSize`, `ControlSize`, `Placeholder`, `Readonly`, `Required`, `Visible`, `Enabled`.

| Component | Description |
|-----------|-------------|
| `TForm` | HTML `<form>` wrapper. Properties: `URL`, `Method`, `Target`, `Validator`, `ValidateOnInit`. Events: `onBeforeSubmit`, `onSubmitSuccess`, `onSubmitError`, `onValidateSuccess`, `onValidateError`, `onSuccessAlert`, `onErrorAlert`, `onCancel`. |
| `TEdit` | Text input (`<input>`). Properties: `HTMLInputType` (text, password, email, etc.), `MaxLength`, `Autocomplete`. Event: `onEnterKeyPressed`. |
| `TCheckbox` | Checkbox control. Properties: `CheckedValue`, `UncheckedValue`. |
| `TRadio` | Radio button group. Child `TRadio.item` entries define options. |
| `TDateTimePicker` | Date/time picker (Tempus Dominus). Properties: `DateFormat`, `DateFormatParameter`. |
| `TLOV` | List of Values -- dropdown/autocomplete. Properties: `LOVSource` (data provider), `AjaxURL` for remote search, `Multiple`. Child `TLOVParameter` for parameters. |
| `TFileUpload` | File upload control. |
| `TText` | Multi-line textarea. |
| `THTMLEdit` | Rich text editor (RichTextEditor). |
| `TStatic` | Read-only display value. |
| `TLabel` | Label element. |
| `THidden` | Hidden input field. |
| `TImage` | Image display. |
| `TLink` | Hyperlink. |
| `TButton` | Button with click event. |
| `TButtonDropdown` | Dropdown button with menu items. |

### Data Layer

| Component | Description |
|-----------|-------------|
| `TDataProvider` | Abstract base for all data sources. Properties: `AutoOpenAllowed`, `Result`, `RowCount`, `TotalRowCount`, `Success`, `DatabaseIndex`, `DataProxy`, `Caching`. |
| `TQuery` | SQL SELECT query. Properties: `sql` (with `:columns`, `:orderby`, `:filter` placeholders), `Filter`, `orderby`, `Offset`, `RowsPerPage`, `CountTotalRows`, `CacheValidity`, `PartitionedBy`. |
| `TStoredProcedure` | Database stored procedure call. Properties: `Procedure`, `TransactionMode`, `GenerateDataResult`, `ErrorCodeParameter`, `ErrorMessageParameter`, `CallbackParameter`. |
| `TQueryGroup` | Groups multiple queries to execute together. |
| `TExternalDataProvider` | Fetches data from an external HTTP endpoint. |
| `TJSONDataProvider` | Parses JSON data sources. |
| `TAPIPost` | Extends TStoredProcedure for HTTP API calls. Properties: `URL`, `URLPath`, `HTTPRequestMethod`. TDBParam children serialized to JSON body. |
| `TDBField` | Maps a database field to a component value. Properties: `FieldName`, `Value`, `DBValue`, `DataType` (string, integer, float, date, datetime, time, bool, JSON), `NullResultParameter`, `ParseValue`. Event: `onSetValue` (can modify value). |
| `TDBParam` | Stored procedure parameter binding. Properties: `ParameterName`, `ParameterMode` (IN/OUT/INOUT), `MDB2DataType`, `Value`, `DefaultValue`, `ParameterNameTransformation`. |
| `TDataProxy` | Proxies data calls to a remote server via HTTP. Properties: `URL`, `HTTPRequestMethod`, `AJAXMode`, `TimeOut`. Child `TDataProxyParameter` components define request parameters. |
| `TQueryFilter` | Adds WHERE conditions to a TQuery. Properties: `FieldName`, `Relation` (=, <>, <, >, LIKE, IN, BETWEEN), `Value`, `FilterGroupParameter`. |
| `TQueryFilterGroup` | Groups TQueryFilters with AND/OR logic. |
| `TDPOpen` | Triggers opening of a referenced data provider. |

### Grid System

`TGrid` renders tabular data with filtering, sorting, pagination, transposing, and Excel/chart export.

**Key TGrid Properties:**

| Property | Description |
|----------|-------------|
| `ListSource` | Component reference to TDataProvider |
| `RowsPerPage` | Rows per page (0 = no pagination) |
| `ActivePage` | Current page (1-based) |
| `SortedBy` | Component reference to the currently sorted TGridColumn |
| `SortingDirection` | `ASC` or `DESC` |
| `Scrollable` | Horizontal scrolling (boolean) |
| `ScrollableY` | Vertical scrolling (boolean) |
| `Selectable` | Row selection enabled (boolean) |
| `Transposed` | Transpose rows/columns (boolean) |
| `GridHTMLType` | `table` or `div` rendering mode |
| `UUID` | Unique ID for persistent state |
| `Persistent` | Session key for saving grid state |
| `MasterValue` | Filter value for master-detail relationships |
| `ViewMode` | `GRID` or `CHART` |
| `ShowRefreshButton` | Show refresh button (boolean) |
| `ShowScrollCheckbox` | Show scroll toggle (boolean) |
| `ShowTransposeCheckbox` | Show transpose toggle (boolean) |

**Grid Child Components:**

| Component | Description |
|-----------|-------------|
| `TGridColumn` | Column definition. Properties: `DBField`, `Header`, `Sortable`, `SortingDirection`, `GridFilter`, `MarkChanges`. |
| `TGridFilter` | Column filter definition. Properties: `DefaultRelation`, `Value`, `Name`. |
| `TGridRow` | Row template definition. |
| `TGridRowActions` | Row-level action buttons container. |
| `TGridParameter` | Additional grid parameters. |

**Grid Filtering (client-side):**

Filters are submitted as parameters in the format `gridname_f_N=columnname:relation:value` (slots 1-99). Relations: `=`, `<>`, `<`, `>`, `<=`, `>=`, `LIKE`, `IN`, `BETWEEN`.

**Grid AJAX Refresh:**

`TGrid_submit(sender, target, urldata)` posts form data to the action URL, receives `{html: "..."}` JSON, and replaces the grid DOM. Emits `masterDataChange` for detail grids.

### Composite Components

| Component | Description |
|-----------|-------------|
| `TWizard` | Multi-step wizard. Property: `ActiveStep`. Child `TWizardStep` components define steps with `StepNumber`, `Title`. Events: `onBeforeStep`, `onAfterStep`. |
| `TTabs` | Tabbed interface. Property: `DefaultTabPane`. Child `TTabPane` components with `Name` and `Title`. Event: `onTabChange`. |
| `TIterator` | Loop component. Properties: `ListSource`, `JSONSource`. For each row in the data source, renders all children with propagated field values. Event: `onZeroResult`. `selfRenderer=true`. |
| `TLinkedComponent` | Delegates rendering to a referenced `Component`. |
| `TTimer` | Client-side timer. |
| `TWorkflow` / `TWorkflowStep` | Workflow state machine. |

### Map Components

| Component | Description |
|-----------|-------------|
| `TMap` | Google Maps integration. Properties: `APIKey`, `Latitude`, `Longitude`, `Zoom`, `Width`, `Height`. |
| `TMapSource` | Map data marker. Properties: `Latitude`, `Longitude`, `Title`, `InfoWindow`. |

### PDF & Signatures

| Component | Description |
|-----------|-------------|
| `TPDFPage` | PDF rendering via mPDF. Properties: `Template`, `HeaderContainerName`, `FooterContainerName`, `BodyContainerName`, `CSSFile`, `Watermark`, `PDFConfig` (JSON mPDF config). Uses `%%PARAMETER` token syntax for substitution. |
| `TPAdES` | PAdES digital signatures. Properties: `CertificatePath`, `CertificatePassword`, `Reason`, `Location`, `ContactInfo`. Method: `signPDF()`. |

### Parameters

| Component | Description |
|-----------|-------------|
| `TParameter` | Base parameter component. |
| `TGlobalParameter` | Sets a global Eisodos parameter. |
| `TDataParameter` | Data-bound parameter. |
| `TDataProxyParameter` | Parameter for TDataProxy requests. |
| `TAPIParameter` | Parameter for TExternalDataProvider. |
| `TLOVParameter` | Parameter for TLOV data source. |

---

## Template System

Templates are located in `assets/templates/tholos/` and processed by the Eisodos template engine.

### Naming Convention

| Pattern | Purpose |
|---------|---------|
| `ComponentType.main.template` | Default render template |
| `ComponentType.partial.ID.template` | Partial render (head, foot, row, etc.) |
| `ComponentType.init.head.template` | HTML head items (CSS, JS includes) |
| `ComponentType.init.foot.template` | HTML foot items (scripts) |

### Template Variables

Every component's `generateProps()` makes these available to templates:

- `$prop_*` -- all component properties (lowercased name)
- `$prop_ID` -- full element ID: `{renderID}_{Name}` (e.g., `TRI8a3f_btnSave`)
- `$prop_name` -- component name
- `$prop_componenttype` -- component type name
- `$prop_datavalues` -- generated `data-*` HTML attributes
- `$prop_route` -- route/action path
- `$prop_parent_name` / `$prop_parent_id` -- parent info
- `$content` -- rendered child content
- `$sender` -- sender component name
- `$Tholos_renderID` -- current render ID
- `$Tholos_nonce` -- CSP nonce for inline scripts

### Template Syntax

```html
## Comment line (stripped from output)
$variable_name               <!-- Variable substitution -->
$variable~='default';        <!-- Variable with default value -->
$templateabs_path__name      <!-- Include another template (/ replaced with __) -->

<!-- Callback function (short form) -->
[%_function_name=Tholos\TholosCallback::_eqs;param=prop_visible;value=true;false=hidden;true=</>%]

<!-- Callback function (block form) -->
<%FUNC%
_function_name=Tholos\TholosCallback::_eqs
param=prop_readonly
value=true
true=readonly="readonly"
false=</>
%FUNC%>
```

### Built-in Callback Functions (TholosCallback)

| Function | Parameters | Description |
|----------|------------|-------------|
| `_eq` | param, value, true, false | If parameter equals value, render `true` template; else `false` template |
| `_eqs` | param, value, true, false | Same as `_eq` but returns strings instead of rendering templates |
| `_neq` | param, value, true, false | Not-equals version of `_eq` |
| `_neqs` | param, value, true, false | Not-equals version of `_eqs` |
| `_case` | param, case1..N, else | Switch/case rendering templates |
| `_cases` | param, case1..N, else | Switch/case returning strings |
| `_safehtml` | param | HTML-safe output with `<pre>` wrapping for newlines |
| `_trim` | value | Trim whitespace |
| `_param2` | param | Double parameter resolution |
| `_listToOptions` | separator, options, selected | Generates `<option>` tags from delimited list |
| `_generateListValues` | component_id | Generates TLOV/TRadio option markup |

### Base Template Fragments

These fragments are included by most component templates:

- `TComponent.properties` -- renders `<input type="hidden" data-*>` for component data
- `TComponent.basedata` -- generates `style` and `data-*` attributes (handles visibility)
- `TComponent.labelicon` -- renders label with optional icon
- `TFormControl.labelsize` / `TFormControl.controlsize` -- Bootstrap column classes
- `TControl.baseevents` -- standard control event scripts
- `TControl.customevents` -- custom event binding scripts
- `TControl.helpblock` -- validation/help text block
- `TContainer.baseevents` -- container event scripts

---

## Client-Side JavaScript API

### TholosApplication.js

The global `Tholos` object provides the client-side framework:

**Core Methods:**

```javascript
// Event dispatch -- the central client-side mechanism
Tholos.eventHandler(senderID, targetID, componentType, methodName, routePath, eventData, userParameters)

// Post-submission handler (called after AJAX form submit)
Tholos.action(success, sender, target)

// Data access
Tholos.getData(target)         // Get jQuery data object
Tholos.setData(target, key, value)
Tholos.getObject(target)       // Get jQuery element
Tholos.getComponentType(target)

// UI
Tholos.pageLoader(show, animate)   // Loading indicator
Tholos.showHelp(helpIndex)         // Context help popup

// Utilities
Tholos.EncodeQueryData(data)       // URL-encode object
Tholos.trace(msg, sender, target, eventData)
Tholos.debug(msg, sender, target, eventData)
```

**Component Methods (called via eventHandler):**

| Method | Description |
|--------|-------------|
| `TAction_navigate(sender, target, route, eventData)` | Navigate to a route. Middle-click opens new window. |
| `TControl_getValue(sender, target)` | Get control's current value |
| `TControl_setLabel(sender, target, eventData)` | Update label text |
| `TControl_setRequired(sender, target, eventData)` | Set required attribute |
| `TControl_setVisible(sender, target, eventData)` | Show/hide control |
| `TControl_getVisible(sender, target)` | Get visibility state |
| `TFormControl_setVisible(sender, target, eventData)` | Show/hide form row |
| `TForm_setEnabled(sender, target, eventData)` | Enable/disable entire form |
| `TComponent_setEnabled(sender, target, eventData)` | Enable/disable component |
| `TComponent_setDataParameters(sender, target, route, eventData)` | Update data-* attributes |
| `TLOV_getValue(sender, target)` | Get LOV selected value(s) |
| `TCheckbox_getValue(sender, target)` | Get checkbox state |
| `TRadio_getValue(sender, target)` | Get selected radio value |
| `TRadio_setEnabled(sender, target, eventData)` | Enable/disable radio group |
| `TGrid_submit(sender, target, urldata)` | AJAX grid refresh |
| `TGrid_getValue(sender, target)` | Get selected row value |
| `TGrid_getFilterSQL(sender, target)` | Get current filter SQL |
| `TGrid_setVisible(sender, target, eventData)` | Show/hide grid |
| `TGrid_changeViewMode(formId, viewMode)` | Switch GRID/CHART |

### TGrid.js

Grid-specific functions:

```javascript
TGrid_submit(sender, target, urldata)  // AJAX refresh, updates DOM, emits masterDataChange
TGrid_parseChartData(formId, data)     // Convert grid data to Chart.js format
TGrid_reloadPreviousState(formId)      // Restore saved grid state
```

**Grid Events (jQuery custom events):**
- `masterDataChange` -- emitted when master grid selection changes (for detail grids)
- `masterRefresh` -- emitted when master grid refreshes
- `onAfterRefresh` -- triggered after grid AJAX refresh completes
- `leavingChartTab` -- triggered when switching from chart to grid view

### Client-Side Libraries

- jQuery + jQuery UI
- Bootstrap 5
- Select2 (dropdown/autocomplete)
- Tempus Dominus (date/time picker)
- Chart.js (grid chart view)
- jQuery Resizable Columns (grid column resize)
- jQuery AJAXQ (request queuing)
- RichTextEditor (THTMLEdit)

---

## Response Types

TAction's `ResponseType` property determines how the rendered output is delivered:

| Type | Content-Type | Description |
|------|-------------|-------------|
| `HTML` | text/html | Full HTML5 page |
| `HTMLSnippet` | text/html | Partial HTML (for AJAX loaders) |
| `JSON` | application/json | `{success, errormsg, errorcode, html, callback, data}` |
| `JSONDATA` | application/json | Raw JSON data |
| `XML` | application/xml | XML output |
| `PLAINTEXT` | text/plain | Plain text |
| `PDF` | application/pdf | PDF via mPDF (requires TPDFPage) |
| `BINARY` | varies | File download |
| `PROXY` | varies | Proxied response from TDataProxy |
| `CUSTOM` | varies | Custom response handling |
| `NONE` | -- | No output (side-effects only) |

---

## Caching System

Tholos supports three caching backends configured via `Tholos.CacheMethod`:

| Backend | Setting | Description |
|---------|---------|-------------|
| File | `file` | JSON cache files in `Tholos.CacheDir` |
| Redis | `redis` | Redis server at `Tholos.CacheServer`:`Tholos.CachePort` |
| Memory | `memory` | PHP session-scoped parameters |

### Cache Scope

- **Private** -- scoped to the current session (file prefix: `{sessionID}_{cacheID}`)
- **Global** -- shared across sessions

### TQuery Caching

Set on TQuery via `Caching` property:

| Mode | Description |
|------|-------------|
| `Disabled` | No caching |
| `Enabled` | Cache entire result set |
| `Partitioned` | Cache by `PartitionedBy` field value |

`CacheValidity` sets lifetime in minutes. `CacheSQLConflict` controls behavior when cached SQL doesn't match current query: `ReadCache`, `RewriteCache`, or `DisableCaching`.

### Cache File Structure

- `{prefix}_{cacheID}.cache` -- cached data (JSON)
- `{prefix}_{cacheID}.index` -- metadata (partition, validity, SQL hash, item count)
- `{prefix}_{cacheID}@{partition}.cache` -- partitioned cache segment

---

## Configuration Parameters

Key Eisodos parameters used by Tholos (typically set in application config):

### Application

| Parameter | Description |
|-----------|-------------|
| `Tholos.ApplicationCacheDir` | Directory for compiled .tcd files |
| `Tholos.AccessLog` | Access log file path |
| `Tholos.AccessLog.Format` | Log format (use `%` as variable prefix) |

### Caching

| Parameter | Default | Description |
|-----------|---------|-------------|
| `Tholos.CacheDir` | | Cache files directory |
| `Tholos.CacheMethod` | `file` | `file`, `redis`, or `memory` |
| `Tholos.CacheServer` | `localhost` | Redis host |
| `Tholos.CachePort` | `6379` | Redis port |
| `Tholos.CacheTimeout` | `0.0` | Redis connection timeout |
| `Tholos.CacheLockWait` | `100` | File lock wait (microseconds) |
| `Tholos.CacheLockLoop` | `20` | Max lock retry attempts |

### Boolean Handling

| Parameter | Default | Description |
|-----------|---------|-------------|
| `Tholos.BoolFalse` | `false,f,n,0` | Comma-separated values treated as false |
| `Tholos.BoolTrue` | `true,t,y,1,*` | Comma-separated values treated as true |

### PDF

| Parameter | Description |
|-----------|-------------|
| `Tholos.mPDF` | JSON mPDF configuration |

### Security

| Parameter | Description |
|-----------|-------------|
| `Tholos.CSPEnabled` | Enable Content Security Policy headers |
| `Tholos.CSPJavascriptHosts` | CSP script-src allowed hosts |
| `Tholos.CSPFontHosts` | CSP font-src allowed hosts |
| `Tholos.nonce` | CSP nonce (auto-generated if empty) |

### Debugging

| Parameter | Description |
|-----------|-------------|
| `Tholos.debugLevel` | Server-side debug verbosity |
| `Tholos.debugToFile` | Debug log path (tokens: `SESSIONID`, `TIME`) |
| `Tholos.debugToUrl` | Remote debug endpoint |
| `Tholos.JSDebugLevel` | Client-side JavaScript debug level |

### Grid Defaults

| Parameter | Description |
|-----------|-------------|
| `Tholos.TGrid.Scrollable` | Default horizontal scroll for all grids |
| `Tholos.TGrid.RowsPerPage` | Default rows per page |

---

## TholosBuilder

TholosBuilder is a companion web application used to visually design Tholos applications. It provides:

- **Visual Component Tree** -- JSTree-based hierarchy editor for building component structures
- **Property Editor** -- type-aware property editing with validation
- **Event Editor** -- configure PHP and GUI event handlers with method references
- **Wizards** -- auto-generate common patterns:
  - **Query Wizard** -- creates TQuery + TDBFields from a SELECT statement
  - **Stored Procedure Wizard** -- creates TStoredProcedure + TDBParams from procedure signature
  - **Grid Wizard** -- creates TGrid + TGridColumns from a data source
  - **Edit Form Wizard** -- creates TForm + form controls from field definitions
- **Compilation** -- compiles the component tree into `.tcd` cache files and `_tholos.init`
- **Version Control** -- task-based change tracking with user assignment

### Compilation Output

The compiler (`compile2()`) generates:

1. **`_tholos.init`** -- PHP file containing:
   - `$this->componentTypes[]` -- type definitions with inheritance
   - `$this->componentTypeIndex[]` -- type ID to class path mapping
   - `$this->componentIndex[]` -- component ID to metadata mapping

2. **`{RouteName}.tcd`** -- PHP file per route containing:
   - `$this->componentDefinitions[]` -- array keyed by component ID with:
     - `pid` -- parent ID
     - `h` -- type hierarchy path (e.g., `TStoredProcedure.TDataProvider.TComponent`)
     - `o` -- component name
     - `p` -- JSON-encoded properties (`{n, t, v, c, d}`)
     - `e` -- JSON-encoded events (`{n, t, v, m, p, c, i, a}`)

3. **`{RouteName}.tcs`** -- human-readable source representation (for version control)

---

## Naming Conventions

- All PHP component classes are prefixed with `T` (e.g., `TComponent`, `TGrid`, `TAction`)
- Constructor parameters use trailing underscore: `$componentType_`, `$id_`, `$parent_id_`
- Component property and event keys are **lowercased** internally
- Element IDs in rendered HTML: `{renderID}_{ComponentName}` (e.g., `TRI8a3f_btnSave`)
- Component definitions use compact JSON keys: `n` (name), `t` (type), `v` (value), `c` (component_id), `d` (nodata)
- Template files follow: `ComponentType.purpose.template` pattern
- Grid filter parameters: `gridname_f_N` (N = 1-99)
- Grid state parameters: `TGrid_PropertyName_` prefix