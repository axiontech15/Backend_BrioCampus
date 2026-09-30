from sqlmodel import create_engine

DATABASE_URL = "mysql+pymsql://root:root@3307/briocampus"
engine = create_engine(DATABASE_URL,echo=True)