FROM python:3.11-slim

# Set working directory
WORKDIR /app

# Copy code
COPY . .

# Install dependencies
RUN pip install flask mysql-connector-python pandas scikit-learn python-dateutil

# Expose Flask's default port
EXPOSE 5000

# Run the app
CMD ["python", "recommend.py"]
