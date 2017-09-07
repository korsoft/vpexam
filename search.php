<?php
    include_once 'includes/db_connect.php';
    include_once 'includes/functions.php';
    sec_session_start();
    include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/constants.php';
    $_strPageTitle   = 'Patient Search';
    $_strHeaderTitle = 'SEARCH';
    $_arrStyles[]    = '/style/search.css';
    $_arrStyles[]    = '/style/sortable-theme-minimal.css';
    $_arrScripts[]   = 'https://code.jquery.com/ui/1.11.4/jquery-ui.js';
    $_arrScripts[]   = '/js/search.js';
    $_arrScripts[]   = '/js/sortable.js';
    $_arrScripts[]   = '/js/jquery.paging.js';
    include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/header_physician.php';
?>
                <div class="mainContent">
                    <div class="searchInstr">
                        <span>Search by name, date of birth, or MRN combinations.</span>
                    </div>
                    <div class="searchBoxDiv">
                        <input class="holo-block" id="searchInput" type="text">
                        <div class="searchResultsDiv">

                        </div>
                    </div>
                </div>
<?php
    include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/footer_physician.php';
