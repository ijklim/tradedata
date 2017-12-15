<?php
    header('Content-Type: application/json');
    echo json_encode($items, JSON_FORCE_OBJECT);