<?php

return [

	// this array define list of callable filters
	// which allow to handle output of base handlers

	'subpath' => function ($str) {

		// This filter slice file name to make subpath
		// using to reduce long files list in directory

		return substr($str, 0, 2) . '/' . substr($str, 2, 2);

	},

];

