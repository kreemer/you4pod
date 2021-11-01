FROM spacetabio/roadrunner-alpine:8.0-base-1.5.0

RUN apk add py3-pip && \
    pip install --upgrade youtube_dl

COPY . /app

# rr is pre installed but config should be provided by application.
CMD ["rr", "serve", "-c", ".rr.yaml"]