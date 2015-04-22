<?php

return [

	// this array define list of callable handlers
	// which using in path interpolation

	'ext' => function ($file) {

		return $file->getClientOriginalExtension();

	},

];
