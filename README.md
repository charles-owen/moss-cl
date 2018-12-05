# CourseLib MOSS Submission analysis component

This component supports sending submissions to the CourseLib system to 
the MOSS (Measure of Software Similarity) system for detections of
inappropriate copying.

See http://metlab.cse.msu.edu/courselib/moss for more detail on utilizing the 
cl/moss component.

The MOSS component is dependent on cl/course and adds features to the submission system. 
To install:

```
composer require cl/moss
composer run cl-installer
```

The cl/moss component installs an analysis class that can be attached to an assignment submission. This is an example of how to add MOSS analysis to a submission:

```
	$submission = $assignment->submissions->add_program("project2program", "Project 2 Program");

	$moss = $submission->add_analysis(new \CL\MOSS\MossAnalysis());
	$moss->type = "zip";
	$moss->user = "123456789";
	$moss->language = "cc";
	$moss->include = "#CanadianExperience/MachineLib.*\\.(cpp|h)$#i";
	$moss->exclude = "#(App|MainFrm|stdafx|targetver|XmlNode|" .
		"resource|initialize|DoubleBufferDC|MachineLib|" .
		"Machine|MachineFactory|MachineStandin|MachineDlg|" .
		"Actor|ActorFactory|AnimChannel|AnimChannelAngle|AnimChannelPoint|".
		"CanadianExperience|HaroldFactory|ImageDrawable|Picture|" .
		"PictureFactory|PictureObserver|PolyDrawable|RotatedBitmap|" .
		"SpartyFactory|Timeline|TimelineDlg|ViewEdit|ViewTimeline|" .
		"HeadTop|Drawable|Polygon|WAVFileReader|WavChannel|WavPlayer)\\.(cpp|h)$#i";

	// $moss->limit = 3;
```

#Parameters

##$moss->type

The type property sets the submission type. The only type currently supported is 'zip', where the assignment is submitted in .zip format. 

##$moss->user

This is a user ID assigned by MOSS. MOSS id's are usually assigned to departments or units and you should check with your system administrator to see if a MOSS ID is already available. See the MOSS home page for details on how to request a MOSS ID. The MOSS ID is usually a 9-digit number.

##$moss->language

The language property indicates the computer language for the submissions. Note that not all MOSS languages appear to work, but the specified list is:

"c", "cc", "java", "ml", "pascal", "ada", "lisp", "scheme", "haskell", "fortran", "ascii", "vhdl", "perl", "matlab", "python", "mips", "prolog", "spice", "vb", "csharp", "modula2", "a8086", "javascript", "plsql", "verilog"

##$moss->include

The include property is set to a regular expression that specifies the files that will be sent to MOSS. Since projects often contain files other than source code, this expression is often used to select out the appropriate files based on the file extension.

##$moss->exclude

The exclude property is set to a regular expression that specified files that will be excluded from sending to MOSS. This is a useful tool for excluding provided code files or automatically generated files. Exclusion is done after inclusion. 

##$moss->limit

The limit property limits the number of submissions that will be sent to MOSS. Setting this limit to a small number is extremely useful when first getting the settings correct, since it allows a smaller number of files to be parsed and sent until the include and exclude settings are correct.

#Additional properties

##$moss->atLeast

By default, the MOSS analysis link is only available to Member::INSTRUCTOR or better. This can be changed by using the atLeast property. This is useful if the task of running MOSS is delegated to a teaching assistant:

```php
$moss->atLeast = Member::TA;
```

##$moss->experimental

The experimental property, if set to true, will specify the MOSS experimental server, which is indicated as having the latests version of the analysis software, though perhaps with additional bugs.

#Suggestions

MOSS can sometimes become unresponsive due to load and may reject large numbers of submissions or just fail on large numbers of submissions. The first request to MOSS will return either 'yes', 'no', or an empty string. Only a response of 'yes' will allow the analysis to proceed. A 'no' response usually indicates that the language specified is not supported. An empty string is an indicator of general unavailability. 

## License

Copyright 2016-2018 Michigan State University

Cirsim is released under the MIT license.

* * *

Written and maintained by Charles B. Owen

