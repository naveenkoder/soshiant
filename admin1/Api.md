<p style="font-size: 3rem;font-weight: bolder">REST API Document</p>

## Upload File

### url

{}

### Object Description

This schema defines the structure of an object that can be used in a REST API request, specifically when using Form Data. The object contains the following properties:

- `unique_id`: An integer representing a unique identifier.
- `value`: A string containing a JSON object with key-value pairs. The keys are titles represented as strings, and the values are numbers.
- `more_info`: A string containing additional information. It appears to be a JSON-like structure.
- `is_real`: A boolean indicating whether the object is real or not. It is represented as an integer where 1 represents true and 0 represents false.
- `year`: A string representing the year.
- `period`: A string representing the period.

### Object Structure

The object follows the following structure:

```json
{
  "unique_id": 100,
  "value": "{\"[Title1]\": \"[Value1]\",\"[Title2D]\": \"[Value2]\",\"[Title3]\": \"[Value3]\"}",
  "more_info": "{\"CL1\": \"Value1\", \"CL2\": \"Value2\", \"CL3\": \"Value3\", \"AVG2\": \"Value2\", \"AVG2\": \"Value2\", \"AVG3\": \"Value3\"}",
  "is_real": 1,
  "year": "2023",
  "period": "12"
}