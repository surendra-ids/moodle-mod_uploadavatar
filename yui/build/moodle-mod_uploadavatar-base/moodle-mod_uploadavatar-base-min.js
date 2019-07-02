Online JavaScript Beautifier (v1.10.0)
Beautify, unpack or deobfuscate JavaScript and HTML, make JSON/JSONP readable, etc.

All of the source code is completely free and open, available on GitHub under MIT licence, 
and we have a command-line version, python library and a node package as well.



 
 
 

HTML <style>, <script> formatting:


Additional Settings (JSON):

{}
  End script and style with newline? 
 Support e4x/jsx syntax 
 Use comma-first list style? 
 Detect packers and obfuscators? 
 Preserve inline braces/code blocks? 
 Keep array indentation? 
 Break lines on chained methods? 
 Space before conditional: "if(x)" / "if (x)" 
 Unescape printable chars encoded as \xNN or \uNNNN? 
 Use JSLint-happy formatting tweaks? 
 Indent <head> and <body> sections? 
 Keep indentation on empty lines? 
Use a simple textarea for code input?
Beautify Code (ctrl-enter)
1
YUI.add("moodle-mod_uploadavatar-base", function(e, t) {
2
    M.mod_uploadavatar = M.mod_uploadavatar || {}, M.mod_uploadavatar.base = {
3
        defaultitemwidth: 187,
4
        courseid: 0,
5
        mid: 0,
6
        gallery: 0,
7
        lbg_setup_ran: !1,
8
        options: {
9
            enablecomments: !1,
10
            enablelikes: !1,
11
            mode: "standard"
12
        },
13
        uri: M.cfg.wwwroot + "/mod/uploadavatar/rest.php",
14
        init: function(e, t, n, r, i, s) {
15
            this.courseid = e, this.mid = t, this.gallery = i, s && s.enablecomments && (this.options.enablecomments = !0), s && s.enablelikes && (this.options.enablelikes = !0), s && s.mode && (this.options.mode = s.mode);
16
            if (r || n === "gallery") this.options.mode !== "thebox" ? this.watch_editing_buttons(n) : this.watch_delete_thebox(n);
17
            i === 0 && n === "gallery" && this.add_remove_collection_handler(), r || this.watch_mediasize(), this.setup_sample_link(), this.watch_resize()
18
        },
19
        add_remove_collection_handler: function() {
20
            var t = e.one(".collection.actions .remove");
21
            if (!t) return;
22
            var n = t.hasClass("owner"),
23
                r = {
24
                    title: M.str.mod_uploadavatar.confirmcollectiondelete,
25
                    question: M.str.mod_uploadavatar.removecollectionconfirm,
26
                    yesLabel: M.str.moodle.submit,
27
                    noLabel: M.str.moodle.cancel,
28
                    closeButtonTitle: M.str.moodle.cancel
29
                },
30
                i = {
31
                    "class": "collection",
32
                    id: this.mid
33
                };
34
            this._confirmationListener = this._confirmationListener || t.on("click", function(t) {
35
                t.preventDefault();
36
                var s;
37
                if (!n) s = new M.core.confirm(r), i.action = "remove", s.on("complete-yes", function() {
38
                    this._confirmationListener.detach(), M.mod_uploadavatar.base.delete_object(i)
39
                }, this);
40
                else {
41
                    var o = M.str.mod_uploadavatar.deleteorremovecollection;
42
                    o += '<br/><input type="textbox" name="deleteorremove"/><br/>', o += M.str.mod_uploadavatar.deleteorremovecollectionwarn, r.question = '<div class="deleteorremove">' + o + "</div>", s = new M.core.confirm(r), s.on("complete-yes", function() {
43
                        i.action = "remove";
44
                        var t = e.one('input[name="deleteorremove"]');
45
                        if (t)
46
                            if (t.get("value").toUpperCase() === "DELETE") i.action = "delete";
47
                            else if (t.get("value") !== "") return;
48
                        this._confirmationListener.detach(), M.mod_uploadavatar.base.delete_object(i)
49
                    }, this)
50
                }
Beautify Code (ctrl-enter)
Your Selected Options (JSON):

{
  "indent_size": "4",
  "indent_char": " ",
  "max_preserve_newlines": "5",
  "preserve_newlines": true,
  "keep_array_indentation": false,
  "break_chained_methods": false,
  "indent_scripts": "normal",
  "brace_style": "collapse",
  "space_before_conditional": true,
  "unescape_strings": false,
  "jslint_happy": false,
  "end_with_newline": false,
  "wrap_line_length": "0",
  "indent_inner_html": false,
  "comma_first": false,
  "e4x": false,
  "indent_empty_lines": false
}
Not pretty enough for you?  Report an issue

Browser extensions and other uses
A bookmarklet (drag it to your bookmarks) by Ichiro Hiroshi to see all scripts used on the page,
Chrome, in case the built-in CSS and javascript formatting isn't enough for you:
— Quick source viewer by Tomi Mickelsson (github, blog),
— Javascript and CSS Code beautifier by c7sky,
— jsbeautify-for-chrome by Tom Rix (github),
— Pretty Beautiful JavaScript by Will McSweeney
— Stackoverflow Code Beautify by Making Odd Edit Studios (github).
Firefox: Javascript deminifier by Ben Murphy, to be used together with the firebug (github),
Safari: Safari extension by Sandro Padin,
Opera: Readable JavaScript (github) by Dither,
Opera: Source extension by Deathamns,
Sublime Text 2/3: CodeFormatter, a python plugin by Avtandil Kikabidze, supports HTML, CSS, JS and a bunch of other languages,
Sublime Text 2/3: HTMLPrettify, a javascript plugin by Victor Porof,
Sublime Text 2: JsFormat, a javascript formatting plugin for this nice editor by Davis Clark,
vim: sourcebeautify.vim, a plugin by michalliu (requires node.js, V8, SpiderMonkey or cscript js engine),
vim: vim-jsbeautify, a plugin by Maksim Ryzhikov (node.js or V8 required),
Emacs: Web-beautify formatting package by Yasuyuki Oka,
Komodo IDE: Beautify-js addon by Bob de Haas (github),
C#: ghost6991 ported the javascript formatter to C#,
Go: ditashi has ported the javascript formatter to golang,
Beautify plugin (github) by HookyQR for the Visual Studio Code IDE,
Fiddler proxy: JavaScript Formatter addon,
gEdit tips by Fabio Nagao,
Akelpad extension by Infocatcher,
Beautifier in Emacs write-up by Seth Mason,
Cloud9, a lovely IDE running in a browser, working in the node/cloud, uses jsbeautifier (github),
Devenir Hacker App, a non-free JavaScript packer for Mac,
REST Console, a request debugging tool for Chrome, beautifies JSON responses (github),
mitmproxy, a nifty SSL-capable HTTP proxy, provides pretty javascript responses (github).
wakanda, a neat IDE for web and mobile applications has a Beautifier extension (github).
Burp Suite now has a beautfier extension, thanks to Soroush Dalili,
Netbeans jsbeautify plugin by Drew Hamlett (github).
brackets-beautify-extension for Adobe Brackets by Drew Hamlett (github),
codecaddy.net, a collection of webdev-related tools, assembled by Darik Hall,
editey.com, an interesting and free Google-Drive oriented editor uses this beautifier,
a beautifier plugin for Grunt by Vishal Kadam,
SynWrite editor has a JsFormat plugin (rar, readme),
LIVEditor, a live-editing HTML/CSS/JS IDE (commercial, Windows-only) uses the library,
Doing anything interesting? Write us to team@beautifier.io so we can add your project to the list.

Written by Einar Lielmanis, maintained and evolved by Liam Newman.

We use the wonderful CodeMirror syntax highlighting editor, written by Marijn Haverbeke.

Made with a great help of Jason Diamond, Patrick Hof, Nochum Sossonko, Andreas Schneider, 
Dave Vasilevsky, Vital Batmanov, Ron Baldwin, Gabriel Harrison, Chris J. Shull, Mathias Bynens, 
Vittorio Gambaletta, Stefano Sanfilippo and Daniel Stockman.

Run the tests