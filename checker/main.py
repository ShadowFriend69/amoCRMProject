import requests
import time
import json
import redis
from elasticsearch import Elasticsearch

def check_website(url):
    start = time.time()
    try:
        response = requests.get(url)
        response_time = time.time() - start
        return {
            'url': url,
            'http_code': response.status_code,
            'response_time': response_time
        }
    except requests.RequestException as e:
        return {
            'url': url,
            'error': str(e),
            'response_time': time.time() - start
        }

def main():
    url = "http://example.com"
    result = check_website(url)

    # Save to Redis
    redis_client = redis.StrictRedis(host='localhost', port=6379, db=0)
    redis_client.set(f'last_check_{url}', json.dumps(result))

    # Save to Elasticsearch
    es = Elasticsearch(['http://localhost:9200'])
    es.index(index='website_checks', body=result)

    print(f"Checked {url}: {result}")

if __name__ == "__main__":
    main()
