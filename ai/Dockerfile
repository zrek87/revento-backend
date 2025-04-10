FROM python:3.11-slim

WORKDIR /app
COPY . .

RUN pip install flask mysql-connector-python pandas scikit-learn python-dateutil

EXPOSE 5000

CMD ["python", "recommend.py"]
