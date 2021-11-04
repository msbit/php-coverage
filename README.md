## PHP Coverage

### Usage

`./coverage.php <script-to-profile>`

### Example

#### Skipped Code

Given a file, `hello.php` containing:

```
<?php

if (0) {
    echo "bye\n";
} else {
    echo "hello\n";
}
```

we can generate coverage by running:

```
./coverage.php hello.php 
```

which produces:

```
/Users/tom/Development/msbit/php-coverage/hello.php
 | <?php
 | 
1| if (0) {
0|     echo "bye\n";
 | } else {
1|     echo "hello\n";
 | }
```

showing that the first leg of the `if` statement is executable, but skipped.

#### Counts
Given two files, `function.php` containing:

```
<?php

function doThing($count)
{
    for ($i = 0; $i < $count; $i++) {
        if ($i % 3 === 0) {
            echo "hello\n";
        }
    }
}

doThing(10);

require 'looping.php';
```

and `looping.php` containing:

```
<?php

for ($i = 0; $i < 10; $i++) {
    if ($i % 3 === 0) {
        echo "hello\n";
    }
}
```

we can generate coverage by running:

```
./coverage.php function.php 
```

which produces:

```
/Users/tom/Development/msbit/php-coverage/function.php
   | <?php
   | 
   | function doThing($count) {
 23|   for ($i = 0; $i < $count; $i++) {
 20|     if ($i % 3 === 0) {
  4|       echo "hello\n";
   |     }
   |   }
   | }
   | 
  3| doThing(10);
   | 
  1| require 'looping.php';
/Users/tom/Development/msbit/php-coverage/looping.php
   | <?php
   | 
 23| for ($i = 0; $i < 10; $i++) {
 20|   if ($i % 3 === 0) {
  4|     echo "hello\n";
   |   }
   | }
```

showing that, in both files, the body of the `if` statement is executed only on a subset of the loop iterations.

### Caveats

* Counts are number of op-codes executed for a given line, which leads to any line with more than trivial logic (most lines) reporting greater counts than would seem sensible given the source
* If the script being profiled contains an `exit` command or fails with a fatal error, coverage will not be collected
* The output of the script being profiled is buffered and discarded, so if there is too much output, it will run out of memory
