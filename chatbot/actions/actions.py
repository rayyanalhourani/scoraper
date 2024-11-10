from typing import Any, Text, Dict, List
from rasa_sdk import Action, Tracker
from rasa_sdk.executor import CollectingDispatcher
from fuzzywuzzy import process
import yaml
from pathlib import Path

def load_teams_from_nlu():
    file_path = Path("./data/teamsLookup.yml")
    with file_path.open("r") as file:
        data = yaml.safe_load(file)

    teams = []
    for item in data.get("nlu", []):
        if "lookup" in item and item["lookup"] == "team":
            teams = item.get("examples", "").splitlines()
            teams = [team.strip("- ") for team in teams if team.strip()]
            break
    return teams

# Load teams from the YAML file
TEAMS = load_teams_from_nlu()

class ActionLastMatch(Action):
    def name(self) -> Text:
        return "action_last_match"

    def run(self, dispatcher: CollectingDispatcher,
            tracker: Tracker,
            domain: Dict[Text, Any]) -> List[Dict[Text, Any]]:
        
        team_input = next(tracker.get_latest_entity_values("team"), None)
        
        if team_input:
            # Fuzzy match the input to the nearest team
            best_match, score = process.extractOne(team_input, TEAMS)
            
            # Print debug info
            print(f"User input: {team_input}")
            print(f"Best match: {best_match}, Score: {score}")
            
            if score >= 80:  # Match threshold
                dispatcher.utter_message(text=f"The last match for {best_match} was on...")
            else:
                dispatcher.utter_message(text="Sorry, I couldn't find a team with a similar name. Please try again.")
        else:
            dispatcher.utter_message(text="Please specify the team you're asking about.")
        
        return []

class ActionNextMatch(Action):
    def name(self) -> Text:
        return "action_next_match"

    def run(self, dispatcher: CollectingDispatcher,
            tracker: Tracker,
            domain: Dict[Text, Any]) -> List[Dict[Text, Any]]:
        
        team_input = next(tracker.get_latest_entity_values("team"), None)
        
        if team_input:
            # Fuzzy match the input to the nearest team
            best_match, score = process.extractOne(team_input, TEAMS)
            
            # Print debug info
            print(f"User input: {team_input}")
            print(f"Best match: {best_match}, Score: {score}")
            
            if score >= 80:  # Match threshold
                dispatcher.utter_message(text=f"The next match for {best_match} will be on...")
            else:
                dispatcher.utter_message(text="Sorry, I couldn't find a team with a similar name. Please try again.")
        else:
            dispatcher.utter_message(text="Please specify the team you're asking about.")
        
        return []
