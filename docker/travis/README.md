# How to build a new image

You have to login to docker with `theopenscholar` account.

```
(cd docker/travis)
docker build -t theopenscholar/openscholar-env .
docker push theopenscholar/openscholar-env
```

# How to run locally the travis process

- If you want to run restful test, run `export TEST_SUITE=restful`
- Open `.travis.yml` file and follow `script` steps
