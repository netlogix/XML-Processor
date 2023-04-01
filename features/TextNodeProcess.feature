Feature: run XMLProcessor with TextNodeProcessor

  Scenario: run XMLProcessor
    Given initialize XMLProcessor with "Netlogix\XmlProcessor\Behat\NodeProcessor\TextNodeProcess"
    When process xml with current XMLProcessor instance:
      """
      <root>
        <test>foo</test>
        <test>bar</test>
      </root>
      """
    Then NodeProcessor "Netlogix\XmlProcessor\Behat\NodeProcessor\TextNodeProcess" should return:
      """
      [
        "foo",
        "bar"
      ]
      """