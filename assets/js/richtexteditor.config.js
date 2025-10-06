let tholos_rte_toolbar_advanced = ""
  + "{code,selectall}"
  + "|{undo,redo,removeformat}"
  + "|{cut,copy,paste,inserttemplate,delete,find}"
  + "|{bold,italic,underline,strike,ucase,lcase,superscript,subscript,forecolor,backcolor}"
  + "#{toggleborder,fullscreenenter,fullscreenexit}"
  + " /{justifyleft,justifycenter,justifyright,justifyfull,lineheight}"
  + "|{insertorderedlist,insertunorderedlist,indent,outdent}"
  + "|{insertlink,unlink,insertblockquote,insertemoji,insertchars,inserttable,insertimage,inserthorizontalrule}"
	+ "#{paragraphs:toggle,fontsize:toggle,inlinestyle}"
	+ " /{paragraphs:dropdown | fontsize:dropdown} {paragraphstyle,toggle_paragraphop}";

let tholos_rte_toolbar_simple = ""
  + "{code,selectall}"
  + "|{undo,redo,removeformat}"
  + "|{cut,copy,paste,delete,find}"
  + "|{bold,italic,underline,strike,ucase,lcase,superscript,subscript}"
  + "#{toggleborder,fullscreenenter,fullscreenexit}"
  + " /{justifyleft,justifycenter,justifyright,justifyfull}"
  + "|{insertlink,unlink,insertemoji,insertchars}";

let tholos_rte_config = {
      url_base: "/tholos/assets/js/richtexteditor",
      contentCssUrl: "/assets/css/richtexteditor.css",
      editorResizeMode: "height",
      enterKeyTag: "p",
      subtoolbar_more: null,
      toolbar_advanced: tholos_rte_toolbar_advanced,
      toolbar_simple: tholos_rte_toolbar_simple,
      galleryImages: [],
      skin: "gray"
    };

/* Override on application level in /assets/js/config/richtexteditor.config.js */
let app_rte_inlineStyles = [],
    app_rte_paragraphStyles = [],
    app_rte_imageStyles = [],
    app_rte_linkStyles = [],
    app_rte_htmlTemplates = [];