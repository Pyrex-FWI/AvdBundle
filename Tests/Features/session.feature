Feature: Bundle DigitalDjPool Session
  I want to able to connect on DDP service
  and i must be abble to read a page, extract songs from a page,
  Download a file

  Scenario: Open a fresh session on service
    When I open a new session on DigitalDjPool
    Then Session should be available

  Scenario: Retrieve list of Tracks from a specific page
    When I open a new session on DigitalDjPool
    And I Fetch page "10"
    Then tracks should be available

  Scenario: Download a track from last page
    When I open a new session on DigitalDjPool
    And I Fetch page "10"
    And I Download First track on previous page
    Then Track File should exist in root_path
