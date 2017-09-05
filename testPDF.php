<?php
// Include the autoloader
require_once 'dompdf/autoload.inc.php';

// Reference and use the dompdf class
use Dompdf\Dompdf;

// Instantiate and use the dompdf class
$dompdf = new Dompdf();
$baa = file_get_contents('baa_template.html');
$dompdf->loadHtml($baa);

// (Optional) Setup the paper size and orientation
$dompdf->setPaper('A4', 'portrait');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to Browser
$dompdf->stream();

up vote
17
down vote
favorite
6
I am using Dompdf to create PDF file but I don't know why it doesn't save the created PDF to server.

Any ideas?

    require_once("./pdf/dompdf_config.inc.php");
    $html =
        '<html><body>'.
        '<p>Put your html here, or generate it with your favourite '.
        'templating system.</p>'.
        '</body></html>';

    $dompdf = new DOMPDF();
    $dompdf->load_html($html);
    $dompdf->render();
    file_put_contents('Brochure.pdf', $dompdf->output());
php file pdf-generation save dompdf
shareimprove this question
edited Jan 7 '12 at 19:57

favo
3,34952852
asked Jan 4 '12 at 1:14

user1079810
89126

Version of dompdf? Any PHP errors? – BrianS Jan 4 '12 at 3:02

dompdf 0.5.2, php 5.2.13 – user1079810 Jan 5 '12 at 2:27

I don't see anything that would prevent you from saving, so I'm guessing a server configuration error. Perhaps PHP is unable to write to that directory? If that's the case PHP will report an error. Check your PHP error log or enable error display. – BrianS Jan 5 '12 at 18:20
add a comment
5 Answers
active oldest votes
up vote
27
down vote
accepted
I have just used dompdf and the code was a little different but it worked.

Here it is:

require_once("./pdf/dompdf_config.inc.php");
$files = glob("./pdf/include/*.php");
foreach($files as $file) include_once($file);

$html =
    '<html><body>'.
    '<p>Put your html here, or generate it with your favourite '.
    'templating system.</p>'.
    '</body></html>';

    $dompdf = new DOMPDF();
    $dompdf->load_html($html);
    $dompdf->render();
    $output = $dompdf->output();
    file_put_contents('Brochure.pdf', $output);
Only difference here is that all of the files in the include directory are included.

Other than that my only suggestion would be to specify a full directory path for writing the file rather than just the filename.

shareimprove this answer
edited Sep 18 '12 at 1:20
answered Jan 4 '12 at 3:55

startupsmith
2,51663054

you should change $ouput to $output in the second to last line. it's small, but it will generate an error – liz Sep 18 '12 at 0:32
add a comment

up vote
6
down vote
Try this It's worked me..

require_once("./pdf/dompdf_config.inc.php");


$html =
      '<html><body>'.
      '<p>Put your html here, or generate it with your favourite '.
      'templating system.</p>'.
      '</body></html>';

    $dompdf = new DOMPDF();
    $dompdf->load_html($html);
    $dompdf->render();
    $output = $dompdf->output();
    $file_to_save = './uploads/offerLetter/file2.pdf';
    file_put_contents($file_to_save, $output);
shareimprove this answer
answered Mar 4 '14 at 12:27

Anand
50211017
add a comment
up vote
1
down vote
From their official document

  require_once("dompdf_config.inc.php");
   $html =
       '<html><body>'.
       '<p>Foo</p>'.
       '</body></html>';

$dompdf = new DOMPDF();
$dompdf->load_html($html);

$dompdf->render();

// The next call will store the entire PDF as a string in $pdf

    $pdf = $dompdf->output();

  // You can now write $pdf to disk, store it in a database or stream it
  // to the client.

   file_put_contents("saved_pdf.pdf", $pdf);
shareimprove this answer