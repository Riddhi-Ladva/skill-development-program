skills = {
    "Riddhi": ["Python", "React", "SQL"],
    "Harsh": ["Java", "Python", "AWS"],
    "Raj": ["Python", "SQL"]
}

def extract_unique_skills(skills_dict):
    unique_skills = set()

    for user in skills_dict:
        for skill in skills_dict[user]:
            unique_skills.add(skill)

    return unique_skills


result = extract_unique_skills(skills)
print(result)
