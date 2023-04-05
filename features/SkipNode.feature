Feature: run XMLProcessor with TextNodeProcessor

  Scenario: run XMLProcessor
    Given initialize XMLProcessor with "Netlogix\XmlProcessor\Behat\NodeProcessor\ArrayNodeProcessor"
    When set skipNode to:
    """
    category
    """
    When process xml with current XMLProcessor instance:
      """
      <root name="main">
        <product id="1">foo</product>
        <category id="1">
          <category id="2">
            <product id="3">baz</product>
          </category>
          <category><bar/></category>
        </category>
        <product id="2">bar</product>
      </root>
      """
    Then NodeProcessor "Netlogix\XmlProcessor\Behat\NodeProcessor\ArrayNodeProcessor" should return:
      """
[
    {
        "node": "root",
        "level": 1,
        "attributes": {
            "name": "main"
        },
        "children": [
            {
                "node": "product",
                "level": 2,
                "attributes": {
                    "id": "1"
                },
                "children": [],
                "text": "foo"
            },
            {
                "node": "category",
                "level": 2,
                "attributes": {
                    "id": "1"
                },
                "children": []
            },
            {
                "node": "product",
                "level": 2,
                "attributes": {
                    "id": "2"
                },
                "children": [],
                "text": "bar"
            }
        ]
    }
]
      """