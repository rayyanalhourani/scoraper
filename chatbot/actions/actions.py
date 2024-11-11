from typing import Any, Text, Dict, List
from rasa_sdk import Action, Tracker
from rasa_sdk.executor import CollectingDispatcher
from fuzzywuzzy import process
import yaml
from pathlib import Path
import requests

# Load the teams from the NLU YAML file
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

TEAMS = load_teams_from_nlu()

# Function to get match data from the server
def get_match(type, team):
    try:
        params = {"teamName": team}
        response = requests.get(f"http://localhost:3000/{type}", params=params)
        if response.status_code == 200:
            return response.json().get("result", {})  
        else:
            return {"error": f"Error: {response.status_code}"}
    except requests.RequestException as e:
        print(f"Request failed: {e}")
        return {"error": "Failed to retrieve match data"}

# Message functions
def message_last_match(best_match, match_data):
    date = match_data.get("date", "N/A")
    team1 = match_data.get("team1", "N/A")
    result = match_data.get("result", "N/A")
    team2 = match_data.get("team2", "N/A")
    league = match_data.get("league", "N/A")
    return f"The last match for {best_match} was on {date}: {team1} {result} {team2} in {league}."

def message_next_match(best_match, match_data):
    date = match_data.get("date", "N/A")
    team1 = match_data.get("team1", "N/A")
    time = match_data.get("time", "N/A")
    team2 = match_data.get("team2", "N/A")
    league = match_data.get("league", "N/A")
    return f"The next match for {best_match} will be on {date} at {time}: {team1} vs {team2} in {league}."

class ActionLastMatch(Action):
    def name(self) -> Text:
        return "action_last_match"

    def run(self, dispatcher: CollectingDispatcher,
            tracker: Tracker,
            domain: Dict[Text, Any]) -> List[Dict[Text, Any]]:
        
        team_input = next(tracker.get_latest_entity_values("team"), None)
        print(team_input)
        
        if team_input:
            best_match, score = process.extractOne(team_input, TEAMS)
            print(f"User input: {team_input}")
            print(f"Best match: {best_match}, Score: {score}")
            
            if score >= 80:
                last_match_data = get_match("lastMatch", best_match)
                dispatcher.utter_message(text=message_last_match(best_match, last_match_data))
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
        print(team_input)

        if team_input:
            best_match, score = process.extractOne(team_input, TEAMS)
            print(f"User input: {team_input}")
            print(f"Best match: {best_match}, Score: {score}")
            
            if score >= 80:
                next_match_data = get_match("nextMatch", best_match)
                dispatcher.utter_message(text=message_next_match(best_match, next_match_data))
            else:
                dispatcher.utter_message(text="Sorry, I couldn't find a team with a similar name. Please try again.")
        else:
            dispatcher.utter_message(text="Please specify the team you're asking about.")
        
        return []

class ActionSubmitMatchForm(Action):
    def name(self) -> Text:
        return "action_submit_match_form"

    def run(self, dispatcher: CollectingDispatcher,
            tracker: Tracker,
            domain: Dict[Text, Any]) -> List[Dict[Text, Any]]:
        
        type_input = next(tracker.get_latest_entity_values("type"), None)
        team_input = next(tracker.get_latest_entity_values("team"), None)
        
        if team_input:
            best_match, score = process.extractOne(team_input, TEAMS)
            if score >= 80:
                match_type = "nextMatch" if type_input in ["next", "next match"] else "lastMatch"
                match_data = get_match(match_type, best_match)
                message_function = message_next_match if match_type == "nextMatch" else message_last_match
                dispatcher.utter_message(text=message_function(best_match, match_data))
            else:
                dispatcher.utter_message(text="Sorry, I couldn't find a team with a similar name. Please try again.")
        else:
            dispatcher.utter_message(text="Please specify the team you're asking about.")
        
        return []
