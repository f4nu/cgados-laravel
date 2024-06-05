from openai import OpenAI
import os
from dotenv import load_dotenv
load_dotenv()

apikey = os.getenv('OPENAI_API_KEY')
projectId = 'proj_UnLIzpVau6b1bZULV1mUaQcz'
organizationId = 'org-QD5Yg4tXDEF1VJV9DpT3XeuC'

system = "Agisci come i dipendenti dell'azienda Ferrero specializzata in prodotti dolciari. L'anno è indicato nel prompt. Crea una e-mail di lavoro e di interazione sociale tra i dipendenti dell'azienda. Ogni e-mail deve contenere tra uno e quattro paragrafi. Scegli il mittente in base all'anno indicato e modifica leggermente il soggetto dell'e-mail senza ripeterlo. Rispondi ad ogni prompt con il seguente formato: \
\
Subject: *SOGGETTO E-MAIL* \
\
From: *NOME COGNOME* <*nome.cognome@ferrero.aperture.int*> \
\
To: *nome.cognome@ferrero.aperture.int* \
\
Date: *DATA NEL FORMATO Y-m-d H:i:s* \
\
\
\
*CORPO DEL MESSAGGIO*"

client = OpenAI()
email_number=300
with open('cronistoriaParsed3.txt', 'r') as f:
    lines = f.readlines()
    for line in lines:
        completion = client.chat.completions.create(
            model="gpt-4o",
            max_tokens=1024,
            response_format={
                "type": "text",
            },
            temperature=1.2,
            stream=False,
            messages=[
                {"role": "system", "content": system},
                {"role": "user", "content": line}
            ]
        )

        message = completion.choices[0].message.content
        print(message)
        file_name = str(email_number) + '.txt'
        with open(file_name, 'a') as f:
            f.write(message)

        email_number += 1
