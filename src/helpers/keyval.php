<?php

/*
Serverboy Interchange
Key-value pair storage manager

Copyright 2010 Serverboy Software; Matt Basta

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

*/

$kvstore_name = IXG_KV_STORAGE;
require("helpers/keyval/$kvstore_name.php");
$kvstore_name .= '_driver';
$keyval = new $kvstore_name();
unset($kvstore_name);