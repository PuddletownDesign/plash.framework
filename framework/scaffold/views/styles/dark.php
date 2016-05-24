body {
	background-color: #222;
	color: #ddd;
	border: 0px;
	font: 100 .9em/1.4em Helvetica, Arial, "Lucida Grande", sans-serif;	
}
h1 {
	font: 3em/1.4em Georgia, "Times New Roman", Times, serif;
	margin: 0;	
	padding: 0;
}

/* ---------------------------
	  TAbles
--------------------------- */

thead {
	text-align: left;
	font-size: 1em;	
}
thead th {
	background-color: #000;	
}
tr {
	border-bottom: 1px solid #999;	
}
td {
	background-color: #111;	
}
td, th {
	padding: .3em;
	border: 0px;
}
a {
	color: #948;
	text-decoration: none;
	font-weight: bold;	
}
th.headerSortUp,
th.headerSortDown {
	background-color: #333 !important;
}
th.headerSortUp:after {
	float: right;
	content: "↑ ";	
	margin-left: 1em;
}
th.headerSortDown:after {
	float: right;
	content: "↓ ";	
	margin-left: 1em;
}
.table {
	padding: 0 .2em;	
	background-color: #333;
	color: #aaa;
}

/* ---------------------------
	  Forms
--------------------------- */

form div {
	background-color: #000;	
	margin-bottom: 1em;
	padding: 1em 1em 0em 1em;
	overflow: hidden;
	position: relative;
}
form br {
	display: none;	
}
.varchar input {
	width:25em;	
}
.int input {
	width:2em;
}
input, select {
	margin-bottom: 1em;	
	margin-right: 1em;
}
label .type {
	display: block;
	font-size: .8em;
	margin-bottom: .2em;
	color: #999;
}
label {
	float: left;
	width:10em;
	margin-right: 1em;
	
}
textarea {
	margin-bottom: 1em;
	float: left;
	margin-right: 1em;
}
.int br {
	display: none;	
}
.required {
	color: #948;
	font-size: .8em;	
}
div.error {
	background-color: #134;
	color: #948;
}