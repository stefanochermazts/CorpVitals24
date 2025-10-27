FROM python:3.11-slim

WORKDIR /arelle

RUN apt-get update && apt-get install -y --no-install-recommends \
    git ca-certificates libxml2 libxslt1.1 zlib1g \
    && rm -rf /var/lib/apt/lists/*

RUN git clone https://github.com/Arelle/Arelle.git . \
 && sed -i '/^cx_Oracle/d;/^pyodbc/d' requirements.txt \
 && pip install --no-cache-dir -r requirements.txt

EXPOSE 8080

CMD ["python", "arelleCmdLine.py", "--webserver", "0.0.0.0:8080"]


