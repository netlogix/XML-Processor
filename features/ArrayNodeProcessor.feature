Feature: run XMLProcessor with TextNodeProcessor

  Scenario: run XMLProcessor
    Given initialize XMLProcessor with "Netlogix\XmlProcessor\Behat\NodeProcessor\ArrayNodeProcessor"
    When process xml with current XMLProcessor instance:
      """
      <root name="main">
        <product id="1">foo</product>
        <product id="2">bar</product>
        <category id="1">
          <category id="2">
            <product id="3">baz</product>
          </category>
          <category><bar/></category>
        </category>
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
                "node": "product",
                "level": 2,
                "attributes": {
                    "id": "2"
                },
                "children": [],
                "text": "bar"
            },
            {
                "node": "category",
                "level": 2,
                "attributes": {
                    "id": "1"
                },
                "children": [
                    {
                        "node": "category",
                        "level": 3,
                        "attributes": {
                            "id": "2"
                        },
                        "children": [
                            {
                                "node": "product",
                                "level": 4,
                                "attributes": {
                                    "id": "3"
                                },
                                "children": [],
                                "text": "baz"
                            }
                        ]
                    },
                    {
                        "node": "category",
                        "level": 3,
                        "attributes": [],
                        "children": [
                            {
                                "node": "bar",
                                "level": 4,
                                "attributes": [],
                                "children": []
                            }
                        ]
                    }
                ]
            }
        ]
    }
]
      """