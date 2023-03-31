Feature: run XMLProcessor on a file
  Scenario: run XMLProcessor on a file
    When I run XMLProcessor with
      """
      <test>
        <test2>
          <test3>test</test3>
        </test2>
      </test>
      """
