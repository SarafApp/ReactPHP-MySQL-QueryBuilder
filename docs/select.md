# Select

The <b>select</b> class contains several methods such as :

1. addColumn
2. addColumns
3. addAllColumns
4. addColumnSum
5. addColumnCount
6. addColumnAverage
7. addColumnMax
8. addColumnMin
9. addColumnAsAlias
10. setDistinct
11. compile


### addAllColumns

<b>Select</b> class has a property as an array called `statement`.

At first, this method count length of the statement array, if it's greater than 0,
it's throw an Exception<small>(Some Columns Already Set)</small>,else
statement array just has one element as (*), that returns all rows of the table.

<b>Notice</b>:<small>this method doesn't have input.</small>

````
 public function addAllColumns(): static
    {
        if (count($this->statements) > 0) {
            throw new QueryBuilderException("Some Columns Already Set");
        }

        $this->statements[] = "*";

        return $this;
    }
````

### addColumn 

First of all this method checks two condition :

.






